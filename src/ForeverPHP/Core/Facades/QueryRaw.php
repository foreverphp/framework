<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Database\QueryRaw
 */
class QueryRaw extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Database\QueryRaw::getInstance(); }
}