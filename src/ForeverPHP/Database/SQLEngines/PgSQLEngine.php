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
    private bool $useTransaction = false;
    private bool $unbuffered = false;

    private function setPgSQLError(): void
    {
        $this->errno = 1;
        $this->errorCode = '';
        $this->error = pg_last_error($this->conn);
    }

    public function connect(): bool
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        $this->useTransaction = false;

        $dbName = $this->database ?: $db['database'];

        $this->conn = pg_connect(
            "host={$db['server']} port={$db['port']} dbname={$dbName} user={$db['user']} password={$db['password']}",
        );

        if (!$this->conn) {
            $this->errno = 1;
            $this->error = pg_last_error();
            return false;
        }

        return true;
    }

    public function setUnbuffered(bool $value): void
    {
        $this->unbuffered = $value;
    }

    private function returnDataGenerator(mixed $resultQuery): array
    {
        if ($this->numRows <= 0) {
            return [];
        }

        $resultType = match ($this->queryReturn) {
            'assoc' => PGSQL_ASSOC,
            'both' => PGSQL_BOTH,
            default => PGSQL_NUM,
        };

        return pg_fetch_all($resultQuery, $resultType) ?: [];
    }

    /**
     * Reemplaza los "?" por "$1", "$2", ... que requiere PostgreSQL.
     */
    private function normalizeQueryParameters(): void
    {
        $count = 1;

        $this->query = preg_replace_callback('/\?/', fn() => '$' . $count++, $this->query);
    }

    private function executeInternal(): array|bool|int
    {
        if (!empty($this->parameters)) {
            $this->normalizeQueryParameters();
        }

        $params = array_map(fn($p) => $p['value'], $this->parameters);

        $result = !empty($params)
            ? pg_query_params($this->conn, $this->query, $params)
            : pg_query($this->conn, $this->query);

        if ($result === false) {
            $this->setPgSQLError();
            return false;
        }

        if ($this->isWriteQuery()) {
            $this->numRows = pg_affected_rows($result);
            return $this->numRows;
        }

        $this->numRows = pg_num_rows($result);

        return $this->returnDataGenerator($result);
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
            if (!pg_close($this->conn)) {
                $this->setPgSQLError();
                return false;
            }

            $this->conn = null;
        }

        return true;
    }

    public function beginTransaction(): void
    {
        if ($this->conn !== null) {
            pg_query($this->conn, 'BEGIN');
            $this->useTransaction = true;
        }
    }

    public function commit(): void
    {
        if ($this->conn !== null && $this->useTransaction) {
            pg_query($this->conn, 'COMMIT');
            $this->useTransaction = false;
        }
    }

    public function rollback(): void
    {
        if ($this->conn !== null && $this->useTransaction) {
            pg_query($this->conn, 'ROLLBACK');
            $this->useTransaction = false;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
