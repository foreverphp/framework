<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Core\Cookie
 */
class Cookie extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Core\Cookie::getInstance(); }
}
