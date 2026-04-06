<?php

namespace ForeverPHP\Core\Facades;

use ForeverPHP\Database\Enums\FetchMode;
use ForeverPHP\Database\Enums\ParameterType;

/**
 * @method static \ForeverPHP\Database\QuerySQL using(string $dbSetting)
 * @method static string getDbSetting()
 * @method static void selectDatabase(string $database)
 * @method static \ForeverPHP\Database\QuerySQL query(string $query, FetchMode|string $fetch = FetchMode::NUM)
 * @method static void addParameter(ParameterType|string $type, mixed $value)
 * @method static \ForeverPHP\Database\QuerySQL unbuffered()
 * @method static mixed execute(string $returnType = 'array')
 * @method static bool executeInsertBulk(string $query, array $bulkData)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollback()
 * @method static bool hasError()
 * @method static int getErrorNumber()
 * @method static string getError()
 * @see \ForeverPHP\Database\QuerySQL
 */
class QuerySQL extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Database\QuerySQL::getInstance();
    }
}
