<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Core\Settings.
 */
class Settings extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Core\Settings::getInstance(); }
}
