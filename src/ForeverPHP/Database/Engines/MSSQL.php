<?php namespace ForeverPHP\Database\Engines;

use ForeverPHP\Core\Settings;
use ForeverPHP\Database\DbEngineInterface;

/**
 * Motor MSSQL(Microsoft SQL Server) permite trabajar con este motor de base
 * de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class MSSQL extends BaseEngine implements DbEngineInterface {
    public function connect() {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        $server = $db['server'] . ',' . $db['port'];

        // Me conecto a la base de datos
        $this->link = mssql_pconnect($server, $db['user'], $db['password']);

        if (!$this->link) {
            $this->error = mssql_get_last_message();
            return false;
        }

        if (!mssql_select_db($db['database'])) {
            $this->error = mssql_get_last_message();
            return false;
        }

        return true;
    }

    private function executeQuery() {
        $return = false;

        if ($this->queryType == 'other') {
            if (mssql_query($this->query, $this->link) === true) {
                $return = true;

                $this->error = mssql_get_last_message();
            }
        } else {
            if ($result = mssql_query($this->query, $this->link)) {
                // Conteo de registros
                if ($this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete') {
                    $this->numRows = mssql_rows_affected($this->link);
                } else {
                    $this->numRows = mssql_num_rows($result);

                    if ($this->queryReturn == 'assoc') {
                        $return = mssql_fetch_assoc($result);
                    } elseif ($this->queryReturn == 'both') {
                        $return = mssql_fetch_array($result, MSSQL_BOTH);
                    } elseif ($this->queryReturn == 'num') {
                        $return = mssql_fetch_row($result);
                    }
                }

                $this->error = mssql_get_last_message();

                mssql_free_result($result);
            } else {
                $this->error = mssql_get_last_message();
            }
        }

        return $return;
    }

    /*
     * Como el conector MSSQL de PHP no cuenta con consultas con
     * parametros, se deben emular.
     */
    private function executeQueryWithParameters() {
        if (count($this->parameters) != 0) {
            $newQuery = '';
            $totalParams = substr_count($this->query, '?');
            $posParam = strpos($this->query, '?');

            // Valida si el numero de parametros coincide
            if ($totalParams != count($this->parameters)) {
                $this->error = 'Incorrect number of parameters in the query.';
                return false;
            }

            foreach ($this->parameters as $param => $paramContent) {
                $valueParam = '';

                if ($paramContent['type'] == 'i' || $paramContent['type'] == 'd') {
                    $valueParam = $paramContent['value'];
                } elseif ($paramContent['type'] == 's' || $paramContent['type'] == 'b') {
                    $valueParam = '\'' . $paramContent['value'] . '\'';
                }

                $newQuery = substr_replace($this->query, $valueParam, $posParam, 1);
                $posParam = strpos($newQuery, '?');
                $this->query = $newQuery;
            }
        }

        return $this->executeQuery();
    }

    public function execute() {
        if (count($this->parameters) == 0) {
            return $this->executeQuery();
        } else {
            return $this->executeQueryWithParameters();
        }
    }

    public function disconnect() {
        if ($this->link != null) {
            // Cierro la conexion
            if (!mssql_close($this->link)) {
                $this->error = mssql_get_last_message();
                return false;
            }

            $this->link = null;
        }
    }

    public function __destruct() {
        $this->disconnect();
    }
}
