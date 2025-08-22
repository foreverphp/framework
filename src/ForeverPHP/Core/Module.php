<?php

namespace ForeverPHP\Core;

use ForeverPHP\Core\ClassLoader;
use ForeverPHP\Core\Exceptions\AppException;
use ForeverPHP\Core\Exceptions\ViewException;
use ForeverPHP\Core\Setup;

/**
 * Funciones comunes para los modulos
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */

/**
 * Deberia funcionar de otra forma deberia cargar los modulos y correctar con
 * Module::run($module);
 * o
 * $module = new Module();
 * $module->add(new GreetCommand);
 * $module->run();
 *
 * Los import los debe hacer setup
 * y la carga de modelos y vistas
 * ademas de los contextos globales
 */
class Module
{
    /**
     * Nombre del modulo actual.
     *
     * @var string
     */
    private $moduleName;

    /**
     * Almacena los middlewares agregados.
     *
     * @var array
     */
    private $middlewares = [];

    /**
     * Contiene la instancia singleton de Module.
     *
     * @var \ForeverPHP\Core\Module
     */
    private static $instance;

    public function __construct()
    {
    }

    /**
     * Obtiene o crea la instancia singleton de Module.
     *
     * @return \ForeverPHP\Core\Module
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function exists(string $module): bool
    {
        $modules = Settings::getInstance()->get('project.modules');

        if (in_array($module, $modules)) {
            return true;
        }

        return false;
    }

    private function loadOptional($optional)
    {
        $optionalPath = MODULES_ROOT . DS . $optional . '.php';

        if (file_exists($optionalPath)) {
            require_once $optionalPath;
        }
    }

    public function load(string $module)
    {
        $this->moduleName = $module;

        // Carga los archivos opcionales de las apps
        $this->loadOptional('contexts');
        $this->loadOptional('middlewares');

        // Agrego los directorias al cargador de clases
        ClassLoader::addDirectories([
            MODULES_ROOT . DS . $this->moduleName . DS . 'Models',
            MODULES_ROOT . DS . $this->moduleName . DS . 'Views',
        ]);
    }

    /**
     * Valida si existe un middleware.
     *
     * @param  string $name
     * @return bool
     */
    public function existsMiddleware($name): bool
    {
        if (array_key_exists($name, $this->middlewares)) {
            return true;
        }

        return false;
    }

    /**
     * Agrega un middleware.
     *
     * @param string $name
     * @param callable $callable
     * @return mixed
     */
    public function setMiddleware(string $name, callable $callable)
    {
        if (!is_callable($callable)) {
            return false;
        }

        $this->middlewares[$name] = $callable;
    }

    /**
     * Ejecuta un middleware.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function getMiddleware(string $name, array $arguments = null)
    {
        if ($this->existsMiddleware($name)) {
            return $this->middlewares[$name]();
        }

        return false;
    }

    private function makeResponse($response)
    {
        /**
         * Valida si el valor de retorno de la funcion, es un objeto que
         * implemente ResponseInterface
         */
        if ($response instanceof \ForeverPHP\Http\ResponseInterface) {
            $response->make();
        }
    }

    /**
     * Ejecuta la vista solicitada.
     *
     * @param  mixed $route
     * @return void
     */
    public function run($route)
    {
        if (!is_array($route)) {
            $this->makeResponse($route);
        }

        // Se separa la vista por ".", si es que la vista esta en subcarpetas
        // NOTA: En ForeverPHP los niveles de directorios se separan por "."
        $viewSegments = explode('.', $route['view']);

        // Nombre del metodo a ejecutar
        $method = $route['method'];

        Setup::toDefine('TEMPLATES_PATH', MODULES_ROOT . DS . $this->moduleName . DS . 'Templates' . DS);
        Setup::toDefine('STATIC_PATH', MODULES_ROOT . DS . 'static' . DS);

        $viewPath = '';
        $view = $viewSegments[0];

        if (count($viewSegments) > 1) {
            $view = $viewSegments[count($viewSegments) - 1];

            // Se elimina el ultimo segmento de la vista, que es el nombre del archivo vista
            array_pop($viewSegments);

            // Se unen los segmentos de la vista con el separador de nombres de espacio
            $viewPath = implode('\\', $viewSegments);
            $viewPath .= '\\';
        }

        // Verifico que la vista hereda de View
        if ($view instanceof \ForeverPHP\View\View) {
            throw new ViewException('La vista (' . $view . ') no hereda de View.');
        }

        // Creo la vista y la ejecuto y le asigno el request a la vista para manipulacion interna
        if (Settings::getInstance()->get('project.usingNamespaces')) {
            $view = '\\Modules\\' . $this->moduleName . '\\Views\\' . $viewPath . $view;
        }

        $v = new $view();

        // Ejecuta la funcion y almacena su valor de retorno
        $returnValue = $v->$method();

        // Se construye la respuesta
        $this->makeResponse($returnValue);
    }

    /**
     * Retorna el nombre del modulo actual.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Importa una vista del modulo en ejecución o una externa.
     *
     * @param  string $view
     * @param  string $appName
     */
    public function importView($view, $appName = null)
    {
        $appName = ($appName === null) ? $this->appName : $appName;
        $importPath = APPS_ROOT . DS . $appName . DS . 'views' . DS . $view . '.php';

        if (file_exists($importPath)) {
            include_once $importPath;
        } else {
            throw new AppException("The object to import ($view) not exists.");
        }
    }
}
