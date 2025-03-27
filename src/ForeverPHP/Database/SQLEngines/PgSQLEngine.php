<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Motor PostgreSQL permite trabajar con este motor de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class PgSQLEngine extends SQLEngine implements SQLEngineInterface
{
    private $useTransaction = false;
    private $stmt = null;

    public function connect()
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        // Las transacciones no estan activas
        $this->useTransaction = false;

        $dbName = ($this->database != false) ? $this->database : $db['database'];

        // Me conecto a la base de datos
        $this->conn = pg_connect(
            "host={$db['server']} port={$db['port']} dbname={$dbName} user={$db['user']} password={$db['password']}"
        );

        if (!$this->conn) {
            $this->errno = 1;
            $this->error = pg_last_error();
            return false;
        }

        return true;
    }

    private function returnDataGenerator($resultQuery)
    {
        $return = [];

        if ($this->numRows > 0) {
            $resultType = PGSQL_NUM;

            $resultType = match ($this->queryReturn) {
                'assoc' => PGSQL_ASSOC,
                'both' => PGSQL_BOTH,
            };

            $return = pg_fetch_all($resultQuery, $resultType);
        }

        return $return;
    }

    private function executeQuery()
    {
        $return = false;
        $stmtName = md5(microtime());

        // Preparar la consulta
        $this->stmt = pg_prepare($this->conn, $stmtName, $this->query);

        // Proceder con la ejecución de la consulta
        if ($this->queryType == 'other') {
            $result = pg_execute($this->conn, $stmtName, []);
            if ($result !== false) {
                $this->numRows = pg_num_rows($result);
                $return = $this->returnDataGenerator($result);
            } else {
                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
            }
        } else {
            $result = pg_execute($this->conn, $stmtName, []);
            if ($result !== false) {
                // Conteo de registros
                if (
                    $this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete'
                ) {
                    $this->numRows = pg_affected_rows($result);
                    $return = true;
                } else {
                    // Obtener el número de filas obtenidas de los metadatos de la consulta
                    $this->numRows = pg_num_rows($result);
                    $return = $this->returnDataGenerator($result);
                }

                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
            } else {
                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
            }
        }

        return $return;
    }

    private function normalizeQueryParameters()
    {
        // Contador para los marcadores de posición
        $count = 1;

        // Reemplazar cada marcador de posición "?" por "$count"
        $this->query = preg_replace_callback('/\?/', function () use (&$count) {
            return '$' . $count++;
        }, $this->query);
    }

    private function executeQueryWithParameters()
    {
        $return = false;
        $stmtName = md5(microtime());

        if (count($this->parameters) != 0) {
            $this->normalizeQueryParameters();

            // Preparar la consulta
            $this->stmt = pg_prepare($this->conn, $stmtName, $this->query);

            // Asignar los parámetros a la consulta
            $params = [];

            foreach ($this->parameters as $keyParam => $paramContent) {
                $params[] = $paramContent['value'];
            }

            // Ejecutar la consulta con los parámetros
            $result = pg_execute($this->conn, $stmtName, $params);
            if ($result !== false) {
                // Conteo de registros
                if (
                    $this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete'
                ) {
                    $this->numRows = pg_affected_rows($result);
                    $return = true;
                } else {
                    // Obtener el número de filas obtenidas de los metadatos de la consulta
                    $this->numRows = pg_num_rows($result);
                    $return = $this->returnDataGenerator($result);
                }

                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
            } else {
                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
            }
        }

        return $return;
    }

    public function execute()
    {
        if (count($this->parameters) == 0) {
            return $this->executeQuery();
        }

        return $this->executeQueryWithParameters();
    }

    public function executeInsertBulk(string $query, array $bulkData)
    {
        // No implementada
    }

    public function disconnect()
    {
        if ($this->conn != null) {
            // Cierro la conexion
            /*if (!pg_close($this->conn)) {
                $this->errno = 1;
                $this->error = pg_last_error($this->conn);
                return false;
            }*/

            $this->conn = null;
        }
    }

    public function beginTransaction()
    {
        if ($this->conn != null) {
            $this->query = 'BEGIN';
            $this->executeQuery();
            $this->useTransaction = true;
        }
    }

    public function commit()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                $this->query = 'COMMIT';
                $this->executeQuery();
            }
        }
    }

    public function rollback()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                $this->query = 'ROLLBACK';
                $this->executeQuery();
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
