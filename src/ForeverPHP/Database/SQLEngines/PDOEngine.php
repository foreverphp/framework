<?php

namespace ForeverPHP\Database\SQLEngines;

use ForeverPHP\Core\Settings;

/**
 * Permite trabajar con cualquier motor de datos que soporte PDO.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class PDOEngine extends SQLEngine implements SQLEngineInterface
{
    private bool $useTransaction = false;
    private ?\PDOStatement $stmt = null;
    private bool $unbuffered = false;

    private function setPDOError(\PDOException $e): void
    {
        $this->error = $e->getMessage();

        // SQLSTATE (string)
        $this->errorCode = $e->errorInfo[0] ?? '';

        // Driver error (int)
        $this->errno = isset($e->errorInfo[1]) && is_numeric($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
    }

    public function connect(): bool
    {
        $db = Settings::getInstance()->get('dbs');
        $db = $db[$this->dbSetting];

        // Las transacciones no estan activas
        $this->useTransaction = false;

        $dsn = "{$db['pdoDriver']}:host={$db['server']};port={$db['port']};dbname={$db['database']}";

        try {
            $this->conn = new \PDO($dsn, $db['user'], $db['password']);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return true;
        } catch (\PDOException $e) {
            $this->setPDOError($e);
            return false;
        }
    }

    /**
     * Habilita modo unbuffered
     */
    public function setUnbuffered(bool $value)
    {
        $this->unbuffered = $value;
    }

    private function returnDataGenerator(\PDOStatement $stmt): array
    {
        if ($this->numRows === 0) {
            return [];
        }

        return match ($this->queryReturn) {
            'assoc' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'both' => $stmt->fetchAll(\PDO::FETCH_BOTH),
            'num' => $stmt->fetchAll(\PDO::FETCH_NUM),
        };
    }

    private function executeInternal(): array|bool|int
    {
        try {
            $this->stmt = $this->conn->prepare($this->query);

            // Ejecutar con o sin parámetros (PDO lo maneja solo)
            if (!empty($this->parameters)) {
                $params = [];

                foreach ($this->parameters as $param) {
                    $params[] = $param['value'];
                }

                $this->stmt->execute($params);
            } else {
                $this->stmt->execute();
            }

            // INSERT / UPDATE / DELETE
            if ($this->isWriteQuery()) {
                return $this->numRows = $this->stmt->rowCount();
            }

            // SELECT / OTHER
            $this->numRows = $this->stmt->rowCount();

            $stmt = $this->stmt;
            return $this->returnDataGenerator($stmt);
        } catch (\PDOException $e) {
            $this->setPDOError($e);
            return false;
        } finally {
            $this->stmt?->closeCursor();
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
        try {
            if (is_multi_array($bulkData)) {
                throw new \Exception('Bulk data must be a single dimensional array.', 1);
            }

            $this->stmt = $this->conn->prepare($query);

            $this->stmt->execute($bulkData);

            return $this->numRows = $this->stmt->rowCount();
        } catch (\PDOException $e) {
            $this->setPDOError($e);
            return 0;
        } finally {
            $this->stmt?->closeCursor();
            $this->stmt = null;
        }
    }

    public function disconnect(): bool
    {
        if ($this->conn != null) {
            $this->conn = null;
        }

        return true;
    }

    public function beginTransaction(): void
    {
        if ($this->conn != null) {
            $this->conn->beginTransaction();
            $this->useTransaction = true;
        }
    }

    public function commit(): void
    {
        if ($this->conn != null && $this->useTransaction) {
            $this->conn->commit();
            $this->useTransaction = false;
        }
    }

    public function rollback(): void
    {
        if ($this->conn != null && $this->useTransaction) {
            $this->conn->rollBack();
            $this->useTransaction = false;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
