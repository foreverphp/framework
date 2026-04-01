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
    private bool $useTransaction = false;
    private ?\mysqli_stmt $stmt = null;
    private bool $unbuffered = false;

    private function setMariaDBError(): void
    {
        $this->errno = $this->conn->errno;
        $this->errorCode = $this->conn->sqlstate;
        $this->error = $this->conn->error;
    }

    public function connect(): bool
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        // Las transacciones no estan activas
        $this->useTransaction = false;

        $dbName = $this->database ?: $db['database'];
        $socket = $db['usingSocket'] ? $db['socket'] : null;

        $this->conn = $socket
            ? new \mysqli($db['server'], $db['user'], $db['password'], $dbName, $db['port'], $socket)
            : new \mysqli($db['server'], $db['user'], $db['password'], $dbName, $db['port']);

        if ($this->conn->connect_errno) {
            $this->setMariaDBError();
            return false;
        }

        return true;
    }

    /**
     * Habilita modo unbuffered
     */
    public function setUnbuffered(bool $value)
    {
        $this->unbuffered = $value;
    }

    private function fetchWithBindResult(\mysqli_stmt $stmt): array
    {
        $rows = [];

        $metadata = $stmt->result_metadata();
        if (!$metadata) {
            return [];
        }

        $fields = $metadata->fetch_fields();
        if (!$fields) {
            return [];
        }

        $row = [];
        $bind = [];

        foreach ($fields as $field) {
            $bind[$field->name] = &$row[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $bind);

        while ($stmt->fetch()) {
            $rowData = [];

            foreach ($row as $key => $value) {
                $rowData[$key] = $value;
            }

            $rows[] = match ($this->queryReturn) {
                'assoc' => $rowData,
                'num' => array_values($rowData),
                'both' => array_merge(array_values($rowData), $rowData),
                default => $rowData,
            };
        }

        return $rows;
    }

    private function returnDataGenerator(\mysqli_result $result): array
    {
        $data = match ($this->queryReturn) {
            'assoc' => $result->fetch_all(MYSQLI_ASSOC),
            'num' => $result->fetch_all(MYSQLI_NUM),
            'both' => $result->fetch_all(MYSQLI_BOTH),
            default => $result->fetch_all(MYSQLI_ASSOC),
        };

        $result->free();

        return $data;
    }

    private function executeInternal(): array|bool|int
    {
        try {
            $this->stmt = $this->conn->stmt_init();

            if (!$this->stmt->prepare($this->query)) {
                $this->setMariaDBError();
                return false;
            }

            // Bind parámetros si existen
            if (!empty($this->parameters)) {
                $types = '';
                $params = [];

                foreach ($this->parameters as $param) {
                    $types .= $param['type'];
                    $params[] = $param['value'];
                }

                // Preparamos el array de referencias
                $bindParams = array_merge([$types], $params);
                $refs = [];
                foreach ($bindParams as $key => $value) {
                    $refs[$key] = &$bindParams[$key];
                }

                call_user_func_array([$this->stmt, 'bind_param'], $refs);
            }

            if (!$this->stmt->execute()) {
                $this->setMariaDBError();
                return false;
            }

            // WRITE QUERY
            if ($this->isWriteQuery()) {
                return $this->numRows = $this->stmt->affected_rows;
            }

            // SELECT / OTHER
            $result = $this->stmt->get_result();

            if ($result instanceof \mysqli_result) {
                $this->numRows = $result->num_rows;

                return $this->returnDataGenerator($result);
            } else {
                // fallback
                if (!$this->unbuffered) {
                    $this->stmt->store_result();
                    $this->numRows = $this->stmt->num_rows;

                    return $this->fetchWithBindResult($this->stmt);
                } else {
                    throw new \RuntimeException('Unbuffered queries not supported in this mode');
                }
            }
        } finally {
            $this->stmt?->close();
            $this->stmt = null;
        }
    }

    public function execute(): array|bool|int
    {
        try {
            $result = $this->executeInternal();
            return $result;
        } finally {
            $this->reset();
        }
    }

    public function executeInsertBulk(string $query, array $bulkData): int
    {
        // No implementada
        return 0;
    }

    public function disconnect(): bool
    {
        if ($this->conn != null) {
            if (!$this->conn->close()) {
                $this->setMariaDBError();
                return false;
            }

            $this->conn = null;
        }

        return true;
    }

    public function beginTransaction(): void
    {
        if ($this->conn != null) {
            $this->conn->autocommit(false);
            $this->conn->begin_transaction();
            $this->useTransaction = true;
        }
    }

    public function commit(): void
    {
        if ($this->conn != null && $this->useTransaction) {
            if ($this->conn->commit()) {
                $this->conn->autocommit(true);
                $this->useTransaction = false;
            }
        }
    }

    public function rollback(): void
    {
        if ($this->conn != null && $this->useTransaction) {
            if ($this->conn->rollback()) {
                $this->conn->autocommit(true);
                $this->useTransaction = false;
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
