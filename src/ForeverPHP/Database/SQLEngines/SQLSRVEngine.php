<?php namespace ForeverPHP\Database\SQLEngines;

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
    public function connect()
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        $dbName = ($this->database != false) ? $this->database : $db['database'];

        $server = $db['server'];

        if ($db['port'] != '') {
            $server .= ',' . $db['port'];
        }

        $connectionInfo = array('UID' => $db['user'], 'PWD' => $db['password'], 'Database' => $dbName);

        // Me conecto a la base de datos
        $this->link = sqlsrv_connect($server, $connectionInfo);

        if (!$this->link) {
            $this->error = sqlsrv_errors();
            return false;
        }

        return true;
    }

    private function executeQuery()
    {
        $return = false;

        if ($this->queryType == 'other') {
            if (sqlsrv_query($this->link, $this->query) !== false) {
                $return = true;

                $this->error = sqlsrv_errors();
            }
        } else {
            if ($stmt = sqlsrv_query($this->link, $this->query)) {
                // Conteo de registros
                if ($this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete') {
                    $this->numRows = sqlsrv_rows_affected($stmt);

                    $return = true;
                } else {
                    $this->numRows = sqlsrv_num_rows($stmt);
                    $fetchType = SQLSRV_FETCH_NUMERIC;

                    if ($this->queryReturn == 'assoc') {
                        $fetchType = SQLSRV_FETCH_ASSOC;
                    } elseif ($this->queryReturn == 'both') {
                        $fetchType = SQLSRV_FETCH_BOTH;
                    }

                    $return = array();

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
            $params = array();

            foreach ($this->parameters as $param => $paramContent) {
                $params[] = $paramContent['value'];
            }

            // Preparo la consulta
            $stmt = sqlsrv_prepare($this->link, $this->query, $params);

            // Se procede con la ejecucion de la consulta
            if ($this->queryType == 'other') {
                if (sqlsrv_execute($stmt) === true) {
                    $return = true;

                    $this->error = sqlsrv_errors();
                }
            } else {
                if (sqlsrv_execute($stmt) === true) {
                    // Conteo de registros
                    if ($this->queryType == 'insert' ||
                        $this->queryType == 'update' ||
                        $this->queryType == 'delete') {
                        $this->numRows = sqlsrv_rows_affected($stmt);

                        $return = true;
                    } else {
                        // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                        $this->numRows = sqlsrv_num_rows($stmt);
                        $fetchType = SQLSRV_FETCH_NUMERIC;

                        if ($this->queryReturn == 'assoc') {
                            $fetchType = SQLSRV_FETCH_ASSOC;
                        } elseif ($this->queryReturn == 'both') {
                            $fetchType = SQLSRV_FETCH_BOTH;
                        }

                        $return = array();

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
        } else {
            return $this->executeQueryWithParameters();
        }
    }

    public function disconnect()
    {
        if ($this->link != null) {
            // Cierro la conexion
            if (!sqlsrv_close($this->link)) {
                $this->error = sqlsrv_errors();
                return false;
            }

            $this->link = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
