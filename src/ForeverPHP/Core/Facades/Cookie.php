<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $name)
 * @method static void set(string $name, string $value, int $expire = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = false)
 * @method static void forever(string $name, string $value, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = false)
 * @method static mixed get(string $name)
 * @method static void remove(string $name, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = false)
 *
 * @see \ForeverPHP\Core\Cookie
 */
class Cookie extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Core\Cookie::getInstance();
    }
}
