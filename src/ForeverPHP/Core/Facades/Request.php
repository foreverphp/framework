<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Http\Request
 */
class Request extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Http\Request::getInstance();
    }
}
