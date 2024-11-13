<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $app)
 * @method static void load(string $app)
 * @method static bool existsMiddleware(string $name)
 * @method static mixed setMiddleware(string $name, \Closure $function)
 * @method static mixed getMiddleware(string $name, array $arguments = null)
 * @method static void run(mixed $route)
 * @method static string getAppName()
 * @method static void importView(string $view, string $appName = null)
 * @see \ForeverPHP\Core\App
 */
class App extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Core\App::getInstance();
    }
}
