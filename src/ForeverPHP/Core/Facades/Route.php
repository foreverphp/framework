<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static void fromApp(string $appName)
 * @method static mixed add(string $route, string $view, array $middlewares = null)
 * @method static string getRoute()
 * @method static string getRouteName()
 * @method static void run()
 *
 * @see \ForeverPHP\Routing\Router
 */
class Route extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Routing\Router::getInstance();
    }
}
