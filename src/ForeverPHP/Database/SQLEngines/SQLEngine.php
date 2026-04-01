<?php

namespace ForeverPHP\Database\SQLEngines;

/**
 * Clase base para los motores de base de datos, que
 * interpreten SQL.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class SQLEngine
{
    protected string $dbSetting;
    protected ?string $database = null;
    protected mixed $conn = null;

    protected string $query = '';
    protected string $queryType = 'other';
    protected string $queryReturn = 'num';

    protected array $parameters = [];

    protected int $errno = 0;
    protected string $errorCode = '';
    protected string $error = '';

    protected int $numRows = 0;

    protected ?array $bulkData = null;

    protected static ?self $instance = null;

    public function __construct(string $dbSetting)
    {
        $this->dbSetting = $dbSetting;
        $this->numRows = 0;
        $this->parameters = [];
        $this->bulkData = null;
    }

    public static function getInstance(string $dbSetting = 'default'): static
    {
        if (static::$instance === null) {
            static::$instance = new static($dbSetting);
        }

        return static::$instance;
    }

    public function selectDatabase($database): void
    {
        $this->database = $database;
    }

    public function query(string $query, string $type = 'other', string $return = 'num'): void
    {
        $this->query = $query;
        $this->queryType = $type;
        $this->queryReturn = $return;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getErrorNumber(): int|string
    {
        return $this->errno;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getNumRows(): int
    {
        return $this->numRows;
    }

    /**
     * Helper común para engines
     */
    protected function isWriteQuery(): bool
    {
        return in_array($this->queryType, ['insert', 'update', 'delete'], true);
    }

    /**
     * Limpia el estado de la consulta (opcional para reutilización segura)
     */
    protected function reset(): void
    {
        $this->query = '';
        $this->queryType = 'other';
        $this->queryReturn = 'num';
        $this->parameters = [];
        $this->numRows = 0;
        $this->bulkData = null;
    }
}
