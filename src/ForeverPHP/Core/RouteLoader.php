<?php namespace ForeverPHP\Core;

/**
 * Permite administrar la carga inicial de las rutas.
 *
 * @since       Version 1.0.0
 */
class RouteLoader {
    /**
     * Almacena todas las rutas.
     *
     * @var array
     */
    private $routes;

    /**
     * Contiene la instancia singleton de RouteLoader.
     *
     * @var \ForeverPHP\Core\RouteLoader
     */
    private static $instance;

    private function RouteLoader() {}

    /**
     * Obtiene o crea la instancia singleton de RouteLoader.
     *
     * @return \ForeverPHP\Core\RouteLoader
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * [register description]
     *
     * @return bool
     */
    public function register() {
        return true;
    }
}
