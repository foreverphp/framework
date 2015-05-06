<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Routing\Redirect
 */
class Redirect extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return new \ForeverPHP\Routing\Redirect; }
}
