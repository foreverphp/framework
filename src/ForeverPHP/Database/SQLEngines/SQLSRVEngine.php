<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Motor SQLSRV (extensión propietaria de Microsoft SQL Server, solo Windows)
 * permite trabajar con este motor de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class SQLSRVEngine extends SQLEngine implements SQLEngineInterface
{
    private bool $useTransaction = false;
    private mixed $stmt = null;
    private bool $unbuffered = false;

    private function setSQLSRVError(): void
    {
        $errors = sqlsrv_errors();

        if (!empty($errors)) {
            $this->errno = $errors[0]['code'] ?? 0;
            $this->errorCode = $errors[0]['SQLSTATE'] ?? '';
            $this->error = $errors[0]['message'] ?? '';
        }
    }

    public function connect(): bool
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        $this->useTransaction = false;

        $dbName = $this->database ?: $db['database'];

        $server = $db['server'];
        if ($db['port'] !== '') {
            $server .= ',' . $db['port'];
        }

        $connectionInfo = [
            'UID' => $db['user'],
            'PWD' => $db['password'],
            'Database' => $dbName,
            'TrustServerCertificate' => $db['trustServerCertificate'],
        ];

        $this->conn = sqlsrv_connect($server, $connectionInfo);

        if (!$this->conn) {
            $this->setSQLSRVError();
            return false;
        }

        return true;
    }

    public function setUnbuffered(bool $value): void
    {
        $this->unbuffered = $value;
    }

    private function returnDataGenerator(mixed $stmt): array
    {
        if ($this->numRows <= 0) {
            return [];
        }

        $fetchType = match ($this->queryReturn) {
            'assoc' => SQLSRV_FETCH_ASSOC,
            'both' => SQLSRV_FETCH_BOTH,
            default => SQLSRV_FETCH_NUMERIC,
        };

        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, $fetchType)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function executeInternal(): array|bool|int
    {
        try {
            $params = array_map(fn($p) => $p['value'], $this->parameters);

            $options = [
                'Scrollable' => $this->unbuffered ? SQLSRV_CURSOR_FORWARD : SQLSRV_CURSOR_STATIC,
            ];

            $this->stmt = sqlsrv_prepare($this->conn, $this->query, !empty($params) ? $params : [], $options);

            if ($this->stmt === false) {
                $this->setSQLSRVError();
                return false;
            }

            if (sqlsrv_execute($this->stmt) === false) {
                $this->setSQLSRVError();
                return false;
            }

            if ($this->isWriteQuery()) {
                $this->numRows = sqlsrv_rows_affected($this->stmt);
                return $this->numRows;
            }

            $this->numRows = sqlsrv_num_rows($this->stmt);

            $stmt = $this->stmt;
            return $this->returnDataGenerator($stmt);
        } finally {
            if ($this->stmt !== null) {
                sqlsrv_free_stmt($this->stmt);
                $this->stmt = null;
            }
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
        if ($this->conn !== null) {
            if (!sqlsrv_close($this->conn)) {
                $this->setSQLSRVError();
                return false;
            }

            $this->conn = null;
        }

        return true;
    }

    public function beginTransaction(): void
    {
        if ($this->conn !== null) {
            sqlsrv_begin_transaction($this->conn);
            $this->useTransaction = true;
        }
    }

    public function commit(): void
    {
        if ($this->conn !== null && $this->useTransaction) {
            sqlsrv_commit($this->conn);
            $this->useTransaction = false;
        }
    }

    public function rollback(): void
    {
        if ($this->conn !== null && $this->useTransaction) {
            sqlsrv_rollback($this->conn);
            $this->useTransaction = false;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
