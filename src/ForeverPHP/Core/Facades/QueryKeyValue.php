<?php namespace ForeverPHP\Core\Facades;

/**
 * @see \ForeverPHP\Database\QueryKeyValue
 */
class QueryKeyValue extends Facade {
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent() { return \ForeverPHP\Database\QueryKeyValue::getInstance(); }
}
