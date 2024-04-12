<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Permite trabajar con cualquier motor de datos que soporte PDO.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class PDOEngine extends SQLEngine implements SQLEngineInterface
{
    private $useTransaction = false;
    private $stmt = null;

    public function connect()
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        // Las transacciones no estan activas
        $this->useTransaction = false;

        $dsn = "{$db['pdoDriver']}:host={$db['server']};port={$db['port']};dbname={$db['database']}";

        try {
            $this->conn = new \PDO($dsn, $db['user'], $db['password']);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->errno = $e->getCode();
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    private function returnDataGenerator()
    {
        $return = [];

        if ($this->numRows > 0) {
            if ($this->queryReturn == 'assoc') {
                $return = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($this->queryReturn == 'both') {
                $return = $this->stmt->fetchAll(\PDO::FETCH_BOTH);
            } elseif ($this->queryReturn == 'num') {
                $return = $this->stmt->fetchAll(\PDO::FETCH_NUM);
            }
        }

        return $return;
    }

    private function executeQuery()
    {
        $return = false;

        try {
            // Se procede con la ejecucion de la consulta
            if ($this->queryType == 'other') {
                $this->numRows = $this->conn->exec($this->query);

                if ($this->numRows > 0) {
                    $return = $this->returnDataGenerator();
                }
            } else {
                $this->stmt = $this->conn->prepare($this->query);
                $this->stmt->execute();

                if (
                    $this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete'
                ) {
                    $this->numRows = $this->stmt->rowCount();

                    $return = true;
                } else {
                    // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                    $this->numRows = $this->stmt->rowCount();

                    // Genera los datos de retorno
                    $return = $this->returnDataGenerator();
                }
            }

            $this->stmt->closeCursor();
        } catch (\PDOException $e) {
            $this->errno = $e->getCode();
            $this->error = $e->getMessage();
        }

        return $return;
    }

    private function executeQueryWithParameters()
    {
        $return = false;

        try {
            if (count($this->parameters) > 0) {
                // Preparo la consulta
                $this->stmt = $this->conn->prepare($this->query);

                // Preparo los parametros
                foreach ($this->parameters as $paramKey => $param) {
                    $paramType = match ($param['type']) {
                        'i' => \PDO::PARAM_INT,
                        'b' => \PDO::PARAM_BOOL,
                        's' => \PDO::PARAM_STR,
                        'd' => \PDO::PARAM_INT
                    };

                    $this->stmt->bindParam($paramKey + 1, $param['value'], $paramType);
                }
                //}

                // Se procede con la ejecucion de la consulta
                if ($this->queryType == 'other') {
                    if ($this->stmt->execute()) {
                        if ($this->numRows > 0) {
                            $return = $this->returnDataGenerator();
                        }
                    }
                } else {
                    if ($this->stmt->execute()) {
                        if (
                            $this->queryType == 'insert' ||
                            $this->queryType == 'update' ||
                            $this->queryType == 'delete'
                        ) {
                            $this->numRows = $this->stmt->rowCount();

                            $return = true;
                        } else {
                            // Se obtiene el numero de filas
                            $this->numRows = $this->stmt->rowCount();

                            // Genera los datos de retorno
                            $return = $this->returnDataGenerator();
                        }
                    }
                }

                $this->stmt->closeCursor();
            }
        } catch (\PDOException $e) {
            $this->errno = $e->getCode();
            $this->error = $e->getMessage();
        }

        return $return;
    }

    public function execute()
    {
        if (count($this->parameters) == 0) {
            return $this->executeQuery();
        } else {
            return $this->executeQueryWithParameters();
        }
    }

    public function executeInsertBulk(string $query, array $bulkData)
    {
        try {
            if (is_multi_array($bulkData)) {
                throw new \Exception('Bulk data must be a single dimensional array.', 1);
            }

            $this->stmt = $this->conn->prepare($query);

            if ($this->stmt->execute($bulkData)) {
                $this->numRows = $this->stmt->rowCount();
            }

            $this->stmt->closeCursor();
        } catch (\PDOException $e) {
            $this->errno = $e->getCode();
            $this->error = $e->getMessage();
        }
    }

    public function disconnect()
    {
        if ($this->conn != null) {
            $this->conn = null;
        }
    }

    public function startTransaction()
    {
        if ($this->conn != null) {
            //mysqli_autocommit($this->conn, false);
            $this->useTransaction = true;
        }
    }

    public function commit()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                //mysqli_commit($this->conn);
            }
        }
    }

    public function rollback()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                //mysqli_rollback($this->conn);
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
