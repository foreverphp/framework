<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Core\Filesystem.
 */
class File extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    //public function getComponent() { return 'files'; }
    protected static function getComponent() { return new \ForeverPHP\Core\Filesystem; }
}
