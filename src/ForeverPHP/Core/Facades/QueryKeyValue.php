<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static void using(array $dbSetting)
 * @method static bool exists(string $key)
 * @method static mixed get(string $key)
 * @method static void set(string $key, mixed $value)
 * @method static void remove(string $key)
 * @method static bool hasError()
 * @method static string getError()
 * @see \ForeverPHP\Database\QueryKeyValue
 */
class QueryKeyValue extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Database\QueryKeyValue::getInstance();
    }
}
