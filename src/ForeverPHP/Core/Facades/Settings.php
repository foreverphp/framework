<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $name)
 * @method static bool set(string $name, mixed $value)
 * @method static mixed get(string $name)
 * @method static string getString(string $key, string $default = '')
 * @method static int getInt(string $key, int $default = 0)
 * @method static float getFloat(string $key, float $default = 0.0)
 * @method static bool getBool(string $key, bool $default = false)
 * @method static array getArray(string $key, array $default = [])
 * @method static bool inDebug()
 * @see \ForeverPHP\Core\Settings
 */
class Settings extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Core\Settings::getInstance();
    }
}
