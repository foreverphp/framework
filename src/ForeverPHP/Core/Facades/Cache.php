<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $key)
 * @method static void set(string $key, string $value)
 * @method static string get(string $key)
 * @method static void remove(string $key)
 * @see \ForeverPHP\Cache\Cache
 */
class Cache extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Cache\Cache::getInstance();
    }
}
