<?php namespace ForeverPHP\Core;

use ForeverPHP\Core\AppException;
use ForeverPHP\Core\ClassLoader;
use ForeverPHP\Core\Setup;
use ForeverPHP\Core\ViewException;

/**
 * Funciones comunes para las aplicaciones
 *
 * @since       Version 1.0.0
 */

/**
 * Deberia funcionar de otra forma deberia cargar las apps y correctar con
 * App::run($app);
 * o
 * $application = new Application();
 * $application->add(new GreetCommand);
 * $application->run();
 *
 * Los import los debe hacer setup
 * y la carga de modelos y vistas
 * ademas de los contextos globales
 */
class App
{
    /**
     * Nombre de la aplicación actual.
     *
     * @var string
     */
    private $appName;

    /**
     * Almacena los middlewares agregados.
     *
     * @var array
     */
    private $middlewares = array();

    /**
     * Contiene la instancia singleton de App.
     *
     * @var \ForeverPHP\Core\App
     */
    private static $instance;

    public function __construct()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de App.
     *
     * @return \ForeverPHP\Core\App
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function exists($app)
    {
        $apps = Settings::getInstance()->get('apps');

        if (in_array($app, $apps)) {
            return true;
        }

        return false;
    }

    private function loadOptional($optional)
    {
        $optionalPath = APPS_ROOT . DS . $optional . '.php';

        if (file_exists($optionalPath)) {
            require_once $optionalPath;
        }
    }

    public function load($app)
    {
        $this->appName = $app;

        // Carga los archivos opcionales de las apps
        $this->loadOptional('contexts');
        $this->loadOptional('middlewares');

        // Agrego los directorias al cargador de clases
        ClassLoader::addDirectories(array(
            APPS_ROOT . DS . $this->appName . DS . 'models',
            APPS_ROOT . DS . $this->appName . DS . 'views',
        ));
    }

    /**
     * Valida si existe un middleware.
     *
     * @param  string $name
     * @return bool
     */
    public function existsMiddleware($name)
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
     * @param clousure
     * @return boolean
     */
    public function setMiddleware($name, $function)
    {
        if (!is_callable($function)) {
            return false;
        }

        $this->middlewares[$name] = $function;
    }

    /**
     * Ejecuta un middleware.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function getMiddleware($name, $arguments = null)
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

        Setup::toDefine('TEMPLATES_PATH', APPS_ROOT . DS . $this->appName . DS . 'Templates' . DS);
        Setup::toDefine('STATIC_PATH', APPS_ROOT . DS . 'static' . DS);

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
            throw new ViewException("La vista ($view) no hereda de View.");
        }

        // Creo la vista y la ejecuto y le asigno el request a la vista para manipulacion interna
        if (Settings::getInstance()->get('usingNamespaces')) {
            $view = '\\Apps\\' . $this->appName . '\\Views\\' . $viewPath . $view;
        }

        $v = new $view();

        // Ejecuta la funcion y almacena su valor de retorno
        $returnValue = $v->$method();

        // Se construye la respuesta
        $this->makeResponse($returnValue);
    }

    /**
     * Retorna el nombre de la aplicación actual.
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * Importa una vista de aplicación en ejecución o una externa.
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
