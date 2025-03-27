<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Motor SQLSRV(Para extención propietaria de Microsoft SQL Server solo
 * Windows) permite trabajar con este motor de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class SQLSRVEngine extends SQLEngine implements SQLEngineInterface
{
    private $useTransaction = false;

    public function connect()
    {
        $db = Settings::getInstance()->get("dbs");
        $db = $db[$this->dbSetting];

        // Las transacciones no estan activas
        $this->useTransaction = false;

        $dbName = $this->database != false ? $this->database : $db["database"];

        $server = $db["server"];

        if ($db["port"] != "") {
            $server .= "," . $db["port"];
        }

        $connectionInfo = [
            "UID" => $db["user"],
            "PWD" => $db["password"],
            "Database" => $dbName,
            "TrustServerCertificate" => $db["trustServerCertificate"],
        ];

        // Me conecto a la base de datos
        $this->conn = sqlsrv_connect($server, $connectionInfo);

        if (!$this->conn) {
            $this->error = sqlsrv_errors();
            return false;
        }

        return true;
    }

    private function executeQuery()
    {
        $return = false;

        if ($this->queryType == "other") {
            if (sqlsrv_query($this->conn, $this->query) !== false) {
                $return = true;

                $this->error = sqlsrv_errors();
            }
        } else {
            if ($stmt = sqlsrv_query($this->conn, $this->query)) {
                // Conteo de registros
                if (
                    $this->queryType == "insert" ||
                    $this->queryType == "update" ||
                    $this->queryType == "delete"
                ) {
                    $this->numRows = sqlsrv_rows_affected($stmt);

                    $return = true;
                } else {
                    $this->numRows = sqlsrv_num_rows($stmt);
                    $fetchType = SQLSRV_FETCH_NUMERIC;

                    $fetchType = match ($this->queryReturn) {
                        "assoc" => SQLSRV_FETCH_ASSOC,
                        "both" => SQLSRV_FETCH_BOTH,
                    };

                    $return = [];

                    while ($row = sqlsrv_fetch_array($stmt, $fetchType)) {
                        array_push($return, $row);
                    }
                }

                $this->error = sqlsrv_errors();

                sqlsrv_free_stmt($stmt);
            } else {
                $this->error = sqlsrv_errors();
            }
        }

        return $return;
    }

    private function executeQueryWithParameters()
    {
        $return = false;

        if (count($this->parameters) != 0) {
            // Prepato los parametros
            $params = [];

            foreach ($this->parameters as $param => $paramContent) {
                $params[] = &$paramContent["value"];
            }

            // Preparo la consulta
            $stmt = sqlsrv_prepare($this->conn, $this->query, $params);

            // Se procede con la ejecucion de la consulta
            if ($this->queryType == "other") {
                if (sqlsrv_execute($stmt) === true) {
                    $return = true;

                    $this->error = sqlsrv_errors();
                }
            } else {
                if (sqlsrv_execute($stmt) === true) {
                    // Conteo de registros
                    if (
                        $this->queryType == "insert" ||
                        $this->queryType == "update" ||
                        $this->queryType == "delete"
                    ) {
                        $this->numRows = sqlsrv_rows_affected($stmt);

                        $return = true;
                    } else {
                        // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                        $this->numRows = sqlsrv_num_rows($stmt);
                        $fetchType = SQLSRV_FETCH_NUMERIC;

                        $fetchType = match ($this->queryReturn) {
                            "assoc" => SQLSRV_FETCH_ASSOC,
                            "both" => SQLSRV_FETCH_BOTH,
                        };

                        $return = [];

                        while ($row = sqlsrv_fetch_array($stmt, $fetchType)) {
                            array_push($return, $row);
                        }
                    }

                    $this->error = sqlsrv_errors();

                    sqlsrv_free_stmt($stmt);
                } else {
                    $this->error = sqlsrv_errors();
                }
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
            if (!sqlsrv_close($this->conn)) {
                $this->error = sqlsrv_errors();
                return false;
            }

            $this->conn = null;
        }
    }

    public function beginTransaction()
    {
        if ($this->conn != null) {
            sqlsrv_begin_transaction($this->conn);
            $this->useTransaction = true;
        }
    }

    public function commit()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                sqlsrv_commit($this->conn);
            }
        }
    }

    public function rollback()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                sqlsrv_rollback($this->conn);
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
