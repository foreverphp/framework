<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Motor MariaDB(compatible con MySQL) permite trabajar con este motor de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class MariaDBEngine extends SQLEngine implements SQLEngineInterface
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

        $socket = false;

        if ($db['usingSocket']) {
            $socket = $db['socket'];
        }

        // Me conecto a la base de datos
        $this->conn = (!$socket)
            ? new \mysqli($db['server'], $db['user'], $db['password'], $dbName, $db['port'])
            : new \mysqli($db['server'], $db['user'], $db['password'], $dbName, $db['port'], $socket);

        if (mysqli_connect_errno()) {
            $this->errno = $this->conn->errno;
            $this->error = $this->conn->connect_error;
            return false;
        }

        return true;
    }

    private function returnDataGenerator()
    {
        $fields = null; // Almacena los nombres de campos afectados en la consulta
        $rows = null; // Almacenas las filas obtenidas de la consulta
        $return = [];

        if ($this->numRows > 0) {
            // Se obtienen los metadatos del resultado para obtener los campos
            $metadata = $this->stmt->result_metadata();
            $mdFields = $metadata->fetch_fields();

            if (count($mdFields) != 0) {
                $fields = [];

                foreach ($mdFields as $field) {
                    if ($this->queryReturn == 'assoc' || $this->queryReturn == 'both') {
                        $fields[$field->name] = &$row[$field->name];
                    } elseif ($this->queryReturn == 'num') {
                        $fields[] = &$row[$field->name];
                    }
                }
            }

            /**
             * Se llama a la funcion 'bind_result' del stmt y como segundo parametro
             * se le entrega una matriz con los nombres de los campos
             */
            call_user_func_array([$this->stmt, 'bind_result'], array_values($fields));

            // Se recorren el resultado de la consulta para llenar $rows
            $rows = [];

            while ($this->stmt->fetch()) {
                $rowData = [];

                /**
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
                $tempRows = [];

                // Crea una nueva matriz para pasar las claves de alfanumerico a numeros
                $keyNums = [];

                for ($i = 0; $i < count($fields); $i++) {
                    array_push($keyNums, $i);
                }

                /**
                 * Al recorrer todas las filas se van agregando los nuevos elementos con
                 * su clave en numero.
                 */
                foreach ($rows as $row => $rowContent) {
                    // Se combinan la matriz de claves en numero y los valores de la fila actual
                    $rowInNums = array_combine($keyNums, array_values($rowContent));

                    // Ahora se unen la matriz con las claves en numero y la original en alfanumerico
                    $tempRows[] = array_merge($rowInNums, $rowContent);
                }

                /**
                 * A la matriz de filas se le entrega su nuevo contenido el cual contiene ambos
                 * formatos de claves en numeros y alfanumerico.
                 */
                $rows = $tempRows;
            }

            // Retorno todos los registros afectados
            $return = $rows;
        }

        return $return;
    }

    private function executeQuery()
    {
        $return = false;

        // Preparo la consulta
        $this->stmt = $this->conn->stmt_init();
        $this->stmt->prepare($this->query);

        // Se procede con la ejecucion de la consulta
        if ($this->queryType == 'other') {
            if ($this->stmt->execute() === true) {
                $this->stmt->store_result();
                $this->numRows = $this->stmt->num_rows();

                // Genera los datos de retorno
                $return = $this->returnDataGenerator();
            } else {
                $this->errno = $this->conn->errno;
                $this->error = $this->conn->error;
            }
        } else {
            if ($this->stmt->execute() === true) {
                // Conteo de registros
                if (
                    $this->queryType == 'insert' ||
                    $this->queryType == 'update' ||
                    $this->queryType == 'delete'
                ) {
                    $this->numRows = $this->stmt->affected_rows;

                    $return = true;
                } else {
                    // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                    $this->stmt->store_result();
                    $this->numRows = $this->stmt->num_rows();

                    // Genera los datos de retorno
                    $return = $this->returnDataGenerator();
                }

                $this->errno = $this->conn->errno;
                $this->error = $this->conn->error;
            } else {
                $this->errno = $this->conn->errno;
                $this->error = $this->conn->error;
            }
        }

        $this->stmt->close();

        return $return;
    }

    private function executeQueryWithParameters()
    {
        $return = false;

        if (count($this->parameters) != 0) {
            // Preparo la consulta
            $this->stmt = $this->conn->stmt_init();
            $this->stmt->prepare($this->query);

            // Asigno los parametros a la consulta por defecto estara en tipo String('s')
            $fieldTypes = '';
            $params = [];

            foreach ($this->parameters as $param => $paramContent) {
                $fieldTypes .= $paramContent['type'];
                $params[] = $paramContent['value'];
            }

            // Añade todos los tipos del campo al inicio de la matriz de parametros
            array_unshift($params, $fieldTypes);

            /**
             * Se reasignan todos los parametros a una nueva matriz con los parametros pasados
             * por referencia
             */
            $paramsRef = [];

            foreach ($params as $key => $value) {
                $paramsRef[$key] = &$params[$key];
            }

            // Se ejecuta la funcion 'bind_param' pasandole todos los parametros en una matriz
            call_user_func_array([$this->stmt, 'bind_param'], array_values($paramsRef));

            // Se procede con la ejecucion de la consulta
            if ($this->queryType == 'other') {
                if ($this->stmt->execute() === true) {
                    $this->stmt->store_result();
                    $this->numRows = $this->stmt->num_rows();

                    // Genera los datos de retorno
                    $return = $this->returnDataGenerator();
                } else {
                    $this->errno = $this->conn->errno;
                    $this->error = $this->conn->error;
                }
            } else {
                if ($this->stmt->execute() === true) {
                    // Conteo de registros
                    if (
                        $this->queryType == 'insert' ||
                        $this->queryType == 'update' ||
                        $this->queryType == 'delete'
                    ) {
                        $this->numRows = $this->stmt->affected_rows;

                        $return = true;
                    } else {
                        // Se obtiene el numero de filas obtenidas de los metadatos de la consulta
                        $this->stmt->store_result();
                        $this->numRows = $this->stmt->num_rows();

                        // Genera los datos de retorno
                        $return = $this->returnDataGenerator();
                    }

                    $this->errno = $this->conn->errno;
                    $this->error = $this->conn->error;
                } else {
                    $this->errno = $this->conn->errno;
                    $this->error = $this->conn->error;
                }
            }

            $this->stmt->close();
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
            if (!$this->conn->close()) {
                $this->errno = $this->conn->errno;
                $this->error = $this->conn->error;
                return false;
            }

            $this->conn = null;
        }
    }

    public function beginTransaction()
    {
        if ($this->conn != null) {
            $this->conn->autocommit(false);
            $this->conn->begin_transaction();

            $this->useTransaction = true;
        }
    }

    public function commit()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                if ($this->conn->commit()) {
                    $this->conn->autocommit(true);
                    $this->useTransaction = false;
                }
            }
        }
    }

    public function rollback()
    {
        if ($this->conn != null) {
            if ($this->useTransaction) {
                if ($this->conn->rollback()) {
                    $this->conn->autocommit(true);
                    $this->useTransaction = false;
                }
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
