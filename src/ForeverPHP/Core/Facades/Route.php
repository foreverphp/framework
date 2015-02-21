<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Routing\Router
 */
class Route extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Routing\Router; } //App::getInstance(); }
}