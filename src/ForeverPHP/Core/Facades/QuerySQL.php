<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Database\QuerySQL
 */
class QuerySQL extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Database\QuerySQL::getInstance(); }
}
