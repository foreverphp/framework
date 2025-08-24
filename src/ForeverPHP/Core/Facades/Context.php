<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $name)
 * @method static void set(string $name, mixed $value, bool $global = false)
 * @method static void setArray(array $values, bool $global)
 * @method static mixed get(string $name, bool $global = false)
 * @method static void useGlobal(string $value)
 * @method static array all()
 * @method static void remove(string $name, bool $global = false)
 * @method static void removeAll()
 *
 * @see \ForeverPHP\View\Context
 */
class Context extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\View\Context::getInstance();
    }
}
