<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static string make($values, $type = 'md5')
 * @see \ForeverPHP\Security\Hash
 */
class Hash extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Security\Hash::getInstance();
    }
}
