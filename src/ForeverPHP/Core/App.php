<?php namespace ForeverPHP\Core;

use ForeverPHP\Core\ClassLoader;
use ForeverPHP\Core\Setup;

/**
 * Funciones comunes para las aplicaciones
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */

/*
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
class AppException extends \Exception {}

class App {
    /**
     * Nombre de la aplicación actual.
     *
     * @var string
     */
    private $appName;

    /**
     * Almacena los decoradores agregados.
     *
     * @var array
     */
    private $decorators = array();

    /**
     * Almacena los contextos globales agregados.
     *
     * @var array
     */
    private $globalContexts = array();

    /**
     * Contiene la instancia singleton de App.
     *
     * @var \ForeverPHP\Core\App
     */
    private static $instance;

    public function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de App.
     *
     * @return \ForeverPHP\Core\App
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function exists($app) {
        $apps = Settings::getInstance()->get('apps');

        if (in_array($app, $apps)) {
            return true;
        }

        return false;
    }

    private function loadOptional($optional) {
        $optionalPath = APPS_ROOT . DS . $optional . '.php';

        if (file_exists($optionalPath)) {
            require_once $optionalPath;
        }
    }

    public function load($app) {
        $this->appName = $app;

        // Carga los archivos opcionales de las apps
        $this->loadOptional('contexts');
        $this->loadOptional('decorators');

        // Agrego los directorias al cargador de clases
        ClassLoader::addDirectories(array(
            APPS_ROOT . DS . $this->appName . DS . 'models',
            APPS_ROOT . DS . $this->appName . DS . 'views'
        ));
    }

    /**
     * Valida si existe un decorador.
     *
     * @param  string $name
     * @return boolean
     */
    public function existsDecorator($name) {
        if (array_key_exists($name, $this->decorators)) {
            return true;
        }

        return false;
    }

    /**
     * Agrega un decorador.
     *
     * @param string $name
     * @param clousure
     * @return boolean
     */
    public function setDecorator($name, $function) {
        if (!is_callable($function)) {
            return false;
        }

        $this->decorators[$name] = $function;
    }

    /**
     * Ejecuta un decorador.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function getDecorator($name, $arguments = null) {
        if ($this->existsDecorator($name)) {
            return $this->decorators[$name]();
        }

        return false;
    }

    /**
     * Agrega uno o mas contextos globales los cuales seran usados
     * luego por al aplicacion en ejecucion.
     *
     * @param mixed $context
     */
    public function setGlobalContext($context) {
        if (is_array($context)) {
            foreach ($context as $c) {
                $ctx = new $c;
                $this->globalContexts = array_merge($this->globalContexts, $ctx->all());
            }
        } else {
            $ctx = new $context;
            $this->globalContexts = array_merge($this->globalContexts, $ctx->all());
        }
    }

    /**
     * Obtiene todos los nombres de los contextos globales.
     *
     * @return array
     */
    public function getGlobalContexts() {
        return $this->globalContexts;
    }

    private function makeResponse($response) {
        /*
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
    public function run($route) {
        if (!is_array($route)) {
            $this->makeResponse($route);
        }

        $view = $route['view'];
        $function = $route['function'];

        Setup::toDefine('TEMPLATES_PATH', APPS_ROOT . DS . $this->appName . DS . 'templates' . DS);
        Setup::toDefine('STATIC_PATH', APPS_ROOT . DS . 'static' . DS);

        // Verifico que la vista hereda de View
        if ($view instanceof \ForeverPHP\View\View) {
            throw new ViewException("La vista ($view) no hereda de View.");
        }

        // Creo la vista y la ejecuto y le asigno el request a la vista para manipulacion interna
        $v = new $view();

        // Ejecuta la funcion y almacena su valor de retorno
        $returnValue = $v->$function();

        // Se construye la respuesta
        $this->makeResponse($returnValue);
    }

    public function getAppName() {
        return $this->appName;
    }
}
