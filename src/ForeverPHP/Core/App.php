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
    private $appName;

    private static $globalContexts = array();

    private static $instance;

    public function __construct() {}

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

        // Agrego los directorias al cargador de clases
        ClassLoader::addDirectories(array(
            APPS_ROOT . DS . $this->appName . DS . 'models',
            APPS_ROOT . DS . $this->appName . DS . 'views'
        ));
    }

    /**
     * Agrega uno o mas contextos globales los cuales seran usados
     * luego por el Response.
     *
     * @param mixed $contexts
     */
    public static function addGlobalContexts($contexts) {
        if (is_array($contexts)) {
            foreach ($contexts as $context) {
                $ctx = new $context;
                self::$globalContexts = array_merge(self::$globalContexts, $ctx->all());
            }
        } else {
            $ctx = new $contexts;
            self::$globalContexts = array_merge(self::$globalContexts, $ctx->all());
        }
    }

    public static function getGlobalContexts() {
        return self::$globalContexts;
    }

    /**
     * Ejecuta la vista solicitada.
     *
     * @param  mixed $route
     * @return void
     */
    public function run($route) {
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

        /*
         * Valida si el valor de retorno de la funcion, es un objeto que
         * implemente ResponseInterface
         */
        if ($returnValue instanceof \ForeverPHP\Http\ResponseInterface) {
            $returnValue->make();
        }

        /*
         * Limpia las cabeceras despues de haber efectuado una redireccion.
         */
        if (!$returnValue instanceof \ForeverPHP\Http\RedirectResponse) {
            SessionManager::getInstance()->set('headersInRedirect', false);
        }
    }

    public function getAppName() {
        return $this->appName;
    }
}
