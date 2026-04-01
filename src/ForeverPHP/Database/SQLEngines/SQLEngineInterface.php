<?php

namespace ForeverPHP\Database\SQLEngines;

/**
 * Interface que deben implementar si o si todos los motores de datos, que
 * interpreten SQL.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
interface SQLEngineInterface
{
    public function __construct(string $dbSetting);

    public function selectDatabase(string $database): void;

    public function connect(): bool;

    public function query(string $query, string $type = 'other', string $return = 'num'): void;

    public function setParameters(array $parameters): void;

    public function execute(): array|bool|int;

    public function executeInsertBulk(string $query, array $bulkData): int;

    public function disconnect(): bool;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
