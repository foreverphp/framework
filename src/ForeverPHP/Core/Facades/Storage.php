<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Filesystem\Filesystem
 */
class Storage extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return new \ForeverPHP\Filesystem\Filesystem; }
}
