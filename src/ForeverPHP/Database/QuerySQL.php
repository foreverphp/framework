<?php

namespace ForeverPHP\Database;

use ForeverPHP\Core\Settings;
use ForeverPHP\Database\Enums\FetchMode;
use ForeverPHP\Database\Enums\ParameterType;

/**
 * Permite la ejecucion de consultas en bruto a la base de datos.
 *
 * @since       Version 0.4.0
 */
class QuerySQL
{
    private string $dbSetting = 'default';
    private bool $connected = false;
    private string|false $database = false;
    private mixed $dbInstance = null;
    private bool $hasError = false;
    private int $errno = 0;
    private string $errorCode = '';
    private string $error = '';
    private array $parameters = [];
    private ?string $query = null;
    private string $queryType = 'select';
    private string $queryReturn = 'num';
    private bool $autocommit = false;
    private bool $useTransaction = false;
    private bool $unbuffered = false;

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
     * @return $this
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Define la configuración de base de datos a utilizar.
     *
     * Permite cambiar dinámicamente la conexión activa antes de ejecutar una consulta.
     *
     * @param string $dbSetting Nombre de la configuración definida en Settings (ej: "default", "analytics")
     * @return $this
     */
    public function using(string $dbSetting): self
    {
        $this->dbSetting = $dbSetting;
        $this->database = false;
        return $this;
    }

    /**
     * Obtiene la configuración de base de datos actualmente en uso.
     *
     * @return string
     */
    public function getDbSetting(): string
    {
        return $this->dbSetting;
    }

    /**
     * Selecciona una base de datos específica dentro de la conexión actual.
     *
     * @param string $database Nombre de la base de datos
     * @return void
     */
    public function selectDatabase(string $database): void
    {
        $this->database = $database;
    }

    private function normalizeFetchMode(FetchMode|string $mode): string
    {
        if ($mode instanceof FetchMode) {
            return $mode->value;
        }

        // Compatibilidad legacy
        $mode = strtolower($mode);

        foreach (FetchMode::cases() as $case) {
            if ($case->value === $mode) {
                return $case->value;
            }
        }

        // fallback seguro
        return FetchMode::NUM->value;
    }

    /**
     * Define la consulta SQL a ejecutar.
     *
     * Detecta automáticamente el tipo de consulta (SELECT, INSERT, UPDATE, DELETE)
     * y el formato de retorno de los resultados.
     *
     * @param string $query Consulta SQL
     * @param FetchMode|string $fetch Tipo de retorno
     * @return $this
     */
    public function query(string $query, FetchMode|string $fetch = FetchMode::NUM): self
    {
        $this->query = $query;

        // Detectar tipo de query
        $queryTrimmed = ltrim($query);
        $firstWord = strtolower(strtok($queryTrimmed, " \n\t"));

        $this->queryType = match ($firstWord) {
            'insert' => 'insert',
            'select' => 'select',
            'update' => 'update',
            'delete' => 'delete',
            default => 'other',
        };

        // Normalizar fetch mode (enum + legacy string)
        $this->queryReturn = $this->normalizeFetchMode($fetch);

        return $this;
    }

    /**
     * Agrega un parámetro para consultas preparadas.
     *
     * Compatible con:
     * - string legacy: "s", "i", "d", "b"
     * - enum moderno: ParameterType
     *
     * @param ParameterType|string $type Tipo de dato
     * @param mixed $value Valor del parámetro
     * @return $this
     */
    public function addParameter(ParameterType|string $type, mixed $value): self
    {
        // Si viene como enum se convierte a string legacy
        if ($type instanceof ParameterType) {
            $type = match ($type) {
                ParameterType::STRING => 's',
                ParameterType::INT => 'i',
                ParameterType::DOUBLE => 'd',
                ParameterType::BOOL => 'b',
            };
        }

        $this->parameters[] = [
            'type' => $type,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Agrega un parámetro de tipo string.
     *
     * @param string $value Valor del parámetro
     * @return $this
     */
    public function addString(string $value): self
    {
        return $this->addParameter(ParameterType::STRING, $value);
    }

    /**
     * Agrega un parámetro de tipo entero.
     *
     * @param int $value Valor del parámetro
     * @return $this
     */
    public function addInt(int $value): self
    {
        return $this->addParameter(ParameterType::INT, $value);
    }

    /**
     * Agrega un parámetro de tipo decimal (double/float).
     *
     * @param float $value Valor del parámetro
     * @return $this
     */
    public function addDouble(float $value): self
    {
        return $this->addParameter(ParameterType::DOUBLE, $value);
    }

    /**
     * Agrega un parámetro booleano.
     *
     * Se adapta automáticamente según el motor:
     * - PostgreSQL → bool
     * - PDO → PARAM_BOOL
     * - MySQL/SQL Server → int (1/0)
     *
     * @param bool $value
     * @return $this
     */
    public function addBool(bool $value): self
    {
        return $this->addParameter(ParameterType::BOOL, $value);
    }

    /**
     * Agrega un parámetro de tipo binario (BLOB).
     *
     * @param mixed $value Valor del parámetro
     * @return $this
     */
    public function addBlob(mixed $value): self
    {
        return $this->addParameter(ParameterType::STRING, $value);
    }

    /**
     * Activa el modo unbuffered para consultas de grandes volúmenes de datos.
     *
     * Reduce el uso de memoria al no cargar todos los resultados en memoria.
     *
     * @return $this
     */
    public function unbuffered(): self
    {
        $this->unbuffered = true;
        return $this;
    }

    private function createInstance(): void
    {
        if (!$this->useTransaction) {
            $this->dbInstance = null;

            // Obtengo la configuracion de la base de datos a utilizar
            $selectDb = Settings::getInstance()->get('dbs');
            $selectDb[$this->dbSetting];
            $dbEngine = $selectDb[$this->dbSetting]['engine'];

            switch ($dbEngine) {
                case 'mariadb':
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\MariaDBEngine($this->dbSetting);
                    break;
                case 'pgsql':
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\PgSQLEngine($this->dbSetting);
                    break;
                case 'sqlsrv':
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\SQLSRVEngine($this->dbSetting);
                    break;
                case 'pdo':
                    $this->dbInstance = new \ForeverPHP\Database\SQLEngines\PDOEngine($this->dbSetting);
                    break;
                default:
                    $this->error = 'Database engine not found.';
                    break;
            }

            if ($this->dbInstance->connect()) {
                $this->connected = true;
            }

            // Pasa el modo unbuffered al engine
            if ($this->unbuffered) {
                $this->dbInstance->setUnbuffered(true);
            }
        }
    }

    /**
     * Ejecuta la consulta SQL definida previamente.
     *
     * @param string $returnType Tipo de retorno: "array" o "json"
     * @return array|string|false Resultado de la consulta o false en caso de error
     *
     * @throws \Exception Si ocurre un error en la ejecución de la consulta
     */
    public function execute(string $returnType = 'array'): array|string|false
    {
        $this->hasError = false;
        $this->errno = 0;
        $this->errorCode = '';
        $this->error = '';
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
                    $this->queryReturn == 'object' ? 'assoc' : $this->queryReturn,
                );
                $this->dbInstance->setParameters($this->parameters);

                if ($result = $this->dbInstance->execute()) {
                    if (strtolower($returnType) == 'json') {
                        $return = json_encode($result, JSON_FORCE_OBJECT);
                    } else {
                        if ($this->queryReturn == 'object') {
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
                // REVISAR LUEGO ESTO NO LE VEO SENTIDO
                $this->errno = (int) $this->dbInstance->getError()[0]['code'];
                $this->errorCode = '';
                $this->error = (string) $this->dbInstance->getError()[0]['message'];
            } else {
                $this->errno = (int) $this->dbInstance->getErrorNumber();
                $this->errorCode = (string) $this->dbInstance->getErrorCode();
                $this->error = (string) $this->dbInstance->getError();
            }

            if (!empty($this->error)) {
                $this->hasError = true;
                $return = false;
            }
        }

        // Se limpian las variables
        $this->parameters = [];
        $this->query = '';
        $this->queryType = 'select';
        $this->queryReturn = 'num';
        $this->unbuffered = false;

        // Agrego este control de error para lanzar una excepción para no tener que usar siempre QuerySQL::hasError
        if (!empty($this->error) && $this->error != null) {
            throw new \Exception("{$this->errorCode}: {$this->error}", is_string($this->errno) ? 0 : $this->errno);
        }

        return $return;
    }

    /**
     * Ejecuta una inserción masiva (bulk insert) usando prepared statements.
     *
     * Prepara la consulta una sola vez y ejecuta el bind + execute por cada fila,
     * lo que es más eficiente y seguro que construir el SQL dinámicamente.
     *
     * Ejemplo de uso:
     * ```php
     * $query = 'INSERT INTO obuma_categorias_producto
     *               (id_categoria, codigo_categoria, nombre, posicion)
     *           VALUES (?, ?, ?, ?)';
     *
     * $bulkData = [
     *     ['types' => 'iisi', 'values' => [1, 100, 'Categoría A', 1]],
     *     ['types' => 'iisi', 'values' => [2, 101, 'Categoría B', 2]],
     * ];
     *
     * QuerySQL::executeInsertBulk($query, $bulkData);
     * ```
     *
     * Tipos de parámetros soportados en 'types':
     * - `i` → integer
     * - `d` → double / float
     * - `s` → string
     * - `b` → bool
     *
     * @param string $query    Consulta SQL con placeholders `?` (debe ser un INSERT)
     * @param array  $bulkData Arreglo de filas a insertar. Cada elemento debe tener:
     *                         - `types`  (string) Cadena de tipos por parámetro (ej: `'iisi'`)
     *                         - `values` (array)  Valores en el mismo orden que los `?`
     *
     * @return int
     *
     * @throws \Exception Si ocurre un error durante la ejecución en el motor de base de datos
     */
    public function executeInsertBulk(string $query, array $bulkData): int
    {
        $affectedRows = 0;
        $this->createInstance();

        if ($this->dbInstance !== null && $this->dbInstance->connect()) {
            $affectedRows = $this->dbInstance->executeInsertBulk($query, $bulkData);
        }

        // Agrego este control de error para lanzar una excepción para no tener que usar siempre QuerySQL::hasError
        if (!empty($this->dbInstance->getError())) {
            throw new \Exception($this->dbInstance->getError(), $this->dbInstance->getErrorNumber());
        }

        // Me desconecto
        if (!$this->useTransaction) {
            $this->releaseInstance();
        }

        return $affectedRows;
    }

    /**
     * Inicia una transacción.
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->createInstance();
        $this->useTransaction = true;
        $this->dbInstance->beginTransaction();
    }

    /**
     * Confirma la transacción actual.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->dbInstance->commit();
        $this->releaseInstance();
    }

    /**
     * Revierte la transacción actual.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->dbInstance->rollback();
        $this->releaseInstance();
    }

    /**
     * Indica si ocurrió un error en la última ejecución.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * Obtiene el número del último error.
     *
     * @return int
     */
    public function getErrorNumber(): int
    {
        return $this->errno;
    }

    /**
     * Obtiene el código del último error.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Obtiene el mensaje del último error.
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    private function releaseInstance(): void
    {
        $this->dbInstance->disconnect();
        $this->connected = false;
    }
}
