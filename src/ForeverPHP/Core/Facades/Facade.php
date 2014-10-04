<?php
namespace ForeverPHP\Core\Facades;

/**
 * Permite crear fachadas de las clases de framework para
 * evitar la necesidad de crear objetos y asi usar alias de
 * clases para usar objetos estaticos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.3.0
 */
class Facade {
    /**
     * Obtiene el objeto detras de la fachada.
     *
     * @return mixed
     */
    private static function getInstanceFacade() {
        return static::resolveFacadeInstance(static::getComponent());
    }

    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    protected static function getComponent() {
        throw new \RuntimeException('La fachada no implementa el metodo getComponent.');
    }

    /**
     * Resuelve la instancia relacionada a la fachada.
     *
     * @param  mixed $name
     * @return mixed
     */
    private static function resolveFacadeInstance($name) {
        if (is_object($name)) {
            return $name;
        }
    }

    /**
     * Controlador de llamadas dinamicas, estaticas hacia el objeto.
     *
     * @param  string $method
     * @param  array $args
     * @return void
     */
    public static function __callStatic($method, $args) {
        $instance = static::getInstanceFacade();

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}
