<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\View\Context
 */
class Context extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\View\Context::getInstance(); }
}
