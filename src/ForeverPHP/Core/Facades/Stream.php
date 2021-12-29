<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Core\Stream
 */
class Stream extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Core\Stream::getInstance();
    }
}
