<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $name)
 * @method static bool set(string $name, mixed $value)
 * @method static mixed get(string $name)
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
