<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Session\SessionManager
 */
class Session extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Session\SessionManager::getInstance();
    }
}
