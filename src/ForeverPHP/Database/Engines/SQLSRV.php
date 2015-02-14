<?php namespace ForeverPHP\Database\Engines;

use ForeverPHP\Core\Settings;
use ForeverPHP\Database\DbEngineInterface;

/**
 * Motor SQLSRV(Para extención propietaria de Microsoft SQL Server solo
 * Windows) permite trabajar con este motor de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class SQLSRV extends BaseEngine implements DbEngineInterface {
    public function connect() {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        $server = $db['server'] . ',' . $db['port'];
        $connectionInfo = array('UID' => $db['user'], 'PWD' => $db['password'], 'Database' => $db['database']);

        // Me conecto a la base de datos
        $this->link = sqlsrv_connect($server, $connectionInfo);

        if (!$this->link) {
            $this->error = sqlsrv_errors();
            return false;
        }

        return true;
    }

    private function executeQuery() {
        $return = false;

        if ($this->queryType == 'other') {
            if (sqlsrv_query($this->link, $this->query) === true) {
                $return = true;

                $this->error = sqlsrv_errors($this->link);
            }
        } else {
            if ($stmt = sqlsrv_query($this->link, $this->query)) {
                // Conteo de registros
                if ($this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete') {
                    $this->numRows = sqlsrv_rows_affected($stmt);
                } else {
                    $this->numRows = sqlsrv_num_rows($stmt);

                    if ($this->queryReturn == 'assoc') {
                        $return = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    } elseif ($this->queryReturn == 'both') {
                        $return = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_BOTH);
                    } elseif ($this->queryReturn == 'num') {
                        $return = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
                    }
                }

                $this->error = sqlsrv_errors($this->link);

                sqlsrv_free_stmt($stmt);
            } else {
                $this->error = sqlsrv_errors($this->link);
            }
        }

        return $return;
    }

    private function executeQueryWithParameters() {
        $return = false;

        if (count($this->parameters) != 0) {
            // Preparo la consulta
            $this->stmt = mysqli_stmt_init($this->link);
            mysqli_stmt_prepare($this->stmt, $this->query);

            // Asigno los parametros a la consulta por defecto estara en tipo String('s')
            $fieldTypes = '';
            $params = array();

            foreach ($this->parameters as $param => $paramContent) {
                $fieldTypes .= $paramContent['type'];
                $params[] = $paramContent['value'];
            }

            // Añade todos los tipos del campo al inicio de la matriz de parametros
            array_unshift($params, $fieldTypes);

            /*
             * Se reasignan todos los parametros a una nueva matriz con los parametros pasados
             * por referencia
             */
            $paramsRef = array();

            foreach ($params as $key => $value) {
                $paramsRef[$key] = &$params[$key];
            }

            // Se ejecuta la funcion 'bind_param' pasandole todos los parametros en una matriz
            call_user_func_array(array($this->stmt, 'bind_param'), $paramsRef);

            // Se procede con la ejecucion de la consulta
            if ($this->queryType == 'other') {
                if ($this->stmt->execute() === true) {
                    $return = true;

                    $this->error = mysqli_error($this->link);
                }
            } else {
                $fields = null; // Almacena los nombres de campos afectados en la consulta
                $rows = null;   // Almacenas las filas obtenidas de la consulta

                if ($this->stmt->execute() === true) {
                    // Conteo de registros
                    if ($this->queryType == 'insert' ||
                        $this->queryType == 'update' ||
                        $this->queryType == 'delete') {
                        $this->numRows = $this->stmt->affected_rows;
                    } else {
                        // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                        $this->stmt->store_result();
                        $this->numRows = $this->stmt->num_rows();

                        // Se obtienen los metadatos del resultado para obtener los campos
                        $metadata = $this->stmt->result_metadata();
                        $mdFields = $metadata->fetch_fields();

                        if (count($mdFields) != 0) {
                            $fields = array();

                            foreach ($mdFields as $field) {
                                if ($this->queryReturn == 'assoc' || $this->queryReturn == 'both') {
                                    $fields[$field->name] = &$row[$field->name];
                                } elseif ($this->queryReturn == 'num') {
                                    $fields[] = &$row[$field->name];
                                }
                            }
                        }

                        /*
                         * Se llama a la funcion 'bind_result' del stmt y como segundo parametro
                         * se le entrega una matriz con los nombres de los campos
                         */
                        call_user_func_array(array($this->stmt, 'bind_result'), $fields);

                        // Se recorren el resultado de la consulta para llenar $rows
                        $rows = array();

                        while ($this->stmt->fetch()) {
                            $rowData = array();

                            /*
                             * Se Deben extraer los datos de $fields con foreach de no acerlo se pisaran
                             * los registro ya que en $fields siempre apunta al ultimo registro leido
                             * por lo tanto si hay 10 registros y se van almacenando en $rows al final
                             * en $rows habran 10 registros pero todos seran iguales al ultimo ya que
                             * todos apuntan a la misma posicion en memoria.
                             */
                            foreach ($fields as $key => $value) {
                                $rowData[$key] = $value;
                            }

                            $rows[] = $rowData;
                        }

                        // Si el tipo de retorno de los registros es Both se procede con lo siguiente
                        if ($this->queryReturn == 'both') {
                            // Primero se crea una matriz temporal
                            $tempRows = array();

                            // Crea una nueva matriz para pasar las claves de alfanumerico a numeros
                            $keyNums = array();

                            for ($i = 0; $i < count($fields); $i++) {
                                array_push($keyNums, $i);
                            }

                            /*
                             * Al recorrer todas las filas se van agregando los nuevos elementos con
                             * su clave en numero
                             */
                            foreach ($rows as $row => $rowContent) {
                                // Se combinan la matriz de claves en numero y los valores de la fila actual
                                $rowInNums = array_combine($keyNums, array_values($rowContent));

                                // Ahora se unen la matriz con las claves en numero y la original en alfanumerico
                                $tempRows[] = array_merge($rowInNums, $rowContent);
                            }

                            /*
                             * A la matriz de filas se le entrega su nuevo contenido el cual contiene ambos
                             * formatos de claves en numeros y alfanumerico.
                             */
                            $rows = $tempRows;
                        }

                        // Retorno todos los registros afectados
                        $return = $rows;
                    }

                    $this->error = mysqli_error($this->link);
                } else {
                    $this->error = mysqli_error($this->link);
                }
            }

            $this->stmt->close();
        }

        return $return;
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
            if (!sqlsrv_close($this->link)) {
                $this->error = sqlsrv_errors($this->link);
                return false;
            }

            $this->link = null;
        }
    }

    public function __destruct() {
        $this->disconnect();
    }
}
