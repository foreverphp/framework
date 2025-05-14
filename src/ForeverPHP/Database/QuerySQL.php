<?php

namespace ForeverPHP\Database;

use ForeverPHP\Core\Settings;

/**
 * Permite la ejecucion de consultas en bruto a la base de datos.
 *
 * @since       Version 0.4.0
 */
class QuerySQL
{
    private $dbSetting = "default";

    private $connected = false;

    private $database = false;

    private $dbInstance = null;

    private $hasError = false;

    private $errno = "";

    private $error = "";

    private $parameters = [];

    private $query = null;

    private $queryType = "select";

    private $queryReturn = "num";

    private $autocommit = false;

    private $useTransaction = false;

    /**
     * Contiene la instancia singleton de QuerySQL.
     *
     * @var \ForeverPHP\Database\QuerySQL
     */
    private static $instance;

    public function __construct()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de QuerySQL.
     *
     * @return \ForeverPHP\Database\QuerySQL
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function using($dbSetting)
    {
        $this->dbSetting = $dbSetting;
        $this->database = false;
    }

    public function selectDatabase($database)
    {
        $this->database = $database;
    }

    public function query($query, $fetch = "num")
    {
        $this->query = $query;

        // Debe detectar que tipo de consulta se va a ejecutar
        $queryInLCase = strtolower($query);

        if (strpos($queryInLCase, "insert") !== false) {
            $this->queryType = "insert";
        } elseif (strpos($queryInLCase, "select") !== false) {
            $this->queryType = "select";
        } elseif (strpos($queryInLCase, "update") !== false) {
            $this->queryType = "update";
        } elseif (strpos($queryInLCase, "delete") !== false) {
            $this->queryType = "delete";
        } else {
            $this->queryType = "other";
        }

        unset($queryInLCase);

        $this->queryReturn = match (strtolower($fetch)) {
            "assoc" => "assoc",
            "both" => "both",
            "object" => "object",
            default => "num",
        };

        return $this;
    }

    public function addParameter($type, $value)
    {
        $count = count($this->parameters);

        $this->parameters[$count] = ["type" => $type, "value" => $value];
    }

    private function createInstance()
    {
        if (!$this->useTransaction) {
            $this->dbInstance = null;

            // Obtengo la configuracion de la base de datos a utilizar
            $selectDb = Settings::getInstance()->get("dbs");
            $selectDb[$this->dbSetting];
            $dbEngine = $selectDb[$this->dbSetting]["engine"];

            switch ($dbEngine) {
                case "mariadb":
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\MariaDBEngine(
                        $this->dbSetting
                    );
                    break;
                case "pgsql":
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\PgSQLEngine(
                        $this->dbSetting
                    );
                    break;
                case "sqlsrv":
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\SQLSRVEngine(
                        $this->dbSetting
                    );
                    break;
                case "pdo":
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\PDOEngine(
                        $this->dbSetting
                    );
                    break;
                default:
                    $this->error = "Database engine not found.";
                    break;
            }

            if ($this->dbInstance->connect()) {
                $this->connected = true;
            }
        }
    }

    public function execute($returnType = "array")
    {
        $this->hasError = false;
        $this->errno = 0;
        $this->error = "";
        $return = false;

        $this->createInstance();

        if ($this->dbInstance != null) {
            if ($this->database != false) {
                $this->dbInstance->selectDatabase($this->database);
            }

            // Me conecto al motor de datos
            if ($this->connected) {
                $this->dbInstance->query(
                    $this->query,
                    $this->queryType,
                    $this->queryReturn == "object"
                        ? "assoc"
                        : $this->queryReturn
                );
                $this->dbInstance->setParameters($this->parameters);

                if ($result = $this->dbInstance->execute()) {
                    if (strtolower($returnType) == "json") {
                        $return = json_encode($result, JSON_FORCE_OBJECT);
                    } else {
                        if ($this->queryReturn == "object") {
                            // object
                            //$return = (object)$result;
                            $return = json_decode(json_encode($result));
                        } else {
                            // array
                            $return = $result;
                        }
                    }
                }

                // Me desconecto
                if (!$this->useTransaction) {
                    $this->releaseInstance();
                }
            }

            // Recupera el ultimo error ocurrido en el motor de datos
            if (is_array($this->dbInstance->getError())) {
                $this->errno = $this->dbInstance->getError()[0]["code"];
                $this->error = $this->dbInstance->getError()[0]["message"];
            } else {
                $this->errno = $this->dbInstance->getErrorNumber();
                $this->error = $this->dbInstance->getError();
            }

            if (!empty($this->error)) {
                $this->hasError = true;
                $return = false;
            }
        }

        // Se limpian las variables
        $this->parameters = [];
        $this->query = "";
        $this->queryType = "select";
        $this->queryReturn = "num";

        // Agrego este control de error para lanzar una excepción para no tener que usar siempre QuerySQL::hasError
        if (!empty($this->error) && $this->error != null) {
            throw new \Exception(
                $this->error,
                is_string($this->errno) ? 0 : $this->errno
            );
        }

        return $return;
    }

    public function executeInsertBulk(string $query, array $bulkData)
    {
        $return = false;

        $this->createInstance();

        if ($this->dbInstance != null) {
            if ($this->connected) {
                $this->dbInstance->executeInsertBulk($query, $bulkData);
            }
        }

        // Agrego este control de error para lanzar una excepción para no tener que usar siempre QuerySQL::hasError
        if (
            !empty($this->dbInstance->getError()) &&
            $this->dbInstance->getError() != null
        ) {
            throw new \Exception(
                $this->dbInstance->getError(),
                is_string($this->dbInstance->getErrorNumber())
                    ? 0
                    : $this->dbInstance->getErrorNumber()
            );
        }

        // Me desconecto
        if (!$this->useTransaction) {
            $this->releaseInstance();
        }

        return $return == null ? false : $return;
    }

    public function beginTransaction()
    {
        $this->createInstance();
        $this->useTransaction = true;
        $this->dbInstance->beginTransaction();
    }

    public function commit()
    {
        $this->dbInstance->commit();
        $this->releaseInstance();
    }

    public function rollback()
    {
        $this->dbInstance->rollback();
        $this->releaseInstance();
    }

    public function hasError()
    {
        return $this->hasError;
    }

    public function getErrorNumber()
    {
        return $this->errno;
    }

    public function getError()
    {
        return $this->error;
    }

    private function releaseInstance()
    {
        $this->dbInstance->disconnect();
        $this->connected = false;
    }
}
