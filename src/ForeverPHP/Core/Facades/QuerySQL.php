<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static void using(string $dbSetting)
 * @method static void selectDatabase(string $database)
 * @method static \ForeverPHP\Database\QuerySQL query(string $query, string $fetch = 'num')
 * @method static void addParameter(string $key, mixed $value)
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
