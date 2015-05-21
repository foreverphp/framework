<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Cache\Cache
 */
class Cache extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Cache\Cache::getInstance(); }
}
