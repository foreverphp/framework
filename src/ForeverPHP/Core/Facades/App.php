<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Session\App.
 */
class App extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Session\App::getInstance(); }
}
