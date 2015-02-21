<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Security\Hash
 */
class Hash extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Security\Hash::getInstance(); }
}