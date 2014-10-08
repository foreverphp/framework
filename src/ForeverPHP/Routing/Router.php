<?php namespace ForeverPHP\Routing;

use ForeverPHP\Core\App;
use ForeverPHP\Core\Exceptions\AppException;
use ForeverPHP\Core\Exceptions\CoreException;
use ForeverPHP\Core\Exceptions\RouterException;
use ForeverPHP\Core\Exceptions\ViewException;
use ForeverPHP\Core\Settings;
use ForeverPHP\Core\Setup;
use ForeverPHP\Http\Request;
use ForeverPHP\Http\Response;
use ForeverPHP\Session\SessionManager;
use ForeverPHP\View\Context;
use ForeverPHP\View\View;

/**
 * Almacena todas las rutas en una matriz para luego ejecutar la ruta
 * solicitada.
 *
 * @since       Version 0.1.0
 */
class Router {
    private static $routes = array();
    private static $complexRoutes = array();

    private static function addSlash($route) {
        $url = $route;

        if (strlen($url) == 0) {
            $url = '/';
        } else {
            // Busca el ultimo slash si no esta se agrega
            if ($url[strlen($url) - 1] != '/') {
                $url .= '/';
            }
        }

        return $url;
    }

    private static function removeSlash($route) {
        $url = $route;

        if ($url[strlen($url) - 1] == '/') {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    private static function parseRoute($route, &$paramsUrl) {
        $newRoute = $route;
        $matches = array();

        // Patrones de busqueda
        $regexFindParam = "/{([0-9A-Za-z\-_]*)([?])?}/";
        $regexParamRequire = "/{([0-9A-Za-z\-_]*)}/";
        $regexParamNoRequire = "/{([0-9A-Za-z\-_]*)\?}/";

        // Patrones de reemplazo
        $replaceParamRequire = "([0-9a-zA-Z\-_]+)";
        $replaceParamNoRequire = "([0-9a-zA-Z\-_]*)";

        preg_match_all($regexFindParam, $route, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $paramsUrl[$match[1]] = count($match) == 3 ? false : true;
            }

            // Reemplazo los parametros requiridos y no requeridos
            $newRoute = preg_replace($regexParamRequire, $replaceParamRequire, $route);
            $newRoute = preg_replace($regexParamNoRequire, $replaceParamNoRequire, $newRoute);

            // Le agrego esta pequeña expresion regular al final para no tener problemas con el slash
            $newRoute .= '[/]?';
        } else {
            $newRoute = self::addSlash($newRoute);
        }

        return $newRoute;
    }

    public static function add($route, $view) {
        $app = null;        // Aplicacion donde esta la vista
        $v = null;          // Vista a buscar
        $function = 'run';  // Funcion por defecto a ejecutar

        // Se valida si es una vista a ejecutar o una funcion anonima
        if (is_string($view)) {
            if (!strpos($view, '.')) {
                throw new RouterException("Revise la ruta ($route) al parecer la ruta a la vista no esta correctamente escrita.");
            }

            // Dividir la vista en app, vista y funcion si es que esta definida
            $view = explode('.', $view);
            $app = $view[0];
            $v = $view[1];

            // Valida si la vista trae un metodo a ejecutar
            if (count($view) == 3) {
                $function = $view[2];
            }
        } else {
            $function = $view;
        }

        // Se valida si la ruta trae parametros por ruta
        $paramsUrl = array();
        $route = self::parseRoute($route, $paramsUrl);

        // Matriz con el contenido de la ruta
        $routeContent = array(
            'app' => $app,
            'view' => $v,
            'function' => $function,
            'paramsUrl' => $paramsUrl
        );

        // Valida si es ruta normal o compleja
        if (count($paramsUrl) == 0) {
            self::$routes[$route] = $routeContent;
        } else {
            self::$complexRoutes[$route] = $routeContent;
        }
    }

    /**
     * Obtiene la ruta correcta
     *
     * @return string        Retorna la ruta con el formato correcto
     */
    private static function getRoute() {
        $url = $_SERVER['REQUEST_URI'];

        // Busco el caracter ? por si se pasaron parametros por GET
        $pos = strpos($url, '?');

        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }

        // Agrega un slash a url para evitar error en la busqueda de ultimo slash
        $url = self::addSlash($url);

        // Retorno la ruta correcta
        return $url;
    }

    private static function loadParamsRoute($route, &$routeContent) {
        $noMatch = true; // Indica si hay o no coincidencias de ruta

        if (count(self::$complexRoutes) > 0) {
            foreach (self::$complexRoutes as $complexRoute => $_routeContent) {
                $regex = '#^' . $complexRoute . '$#m';

                // Busca los parametreos dentro de la ruta
                preg_match_all($regex, $route, $matches, PREG_SET_ORDER);

                if (count($matches) > 0) {
                    $paramsUrl = array();
                    $i = 0; // Indice del parametro

                    // Se saca el primer elemento de las coincidencia ya que no se usara
                    $matches = $matches[0];
                    array_shift($matches);

                    // Se genera la matriz con los parametros para el request
                    foreach ($_routeContent['paramsUrl'] as $paramKey => $paramValue) {
                        if ($matches[$i] != null) {
                            $paramsUrl[$paramKey] = $matches[$i];
                        }

                        $i++;
                    }

                    // Se valida que realmente que vayan parametros en $paramsUrl
                    if (count($paramsUrl) > 0) {
                        Request::register($paramsUrl);
                    }

                    if ($_routeContent['app'] != null) {
                        $routeContent = $_routeContent;
                    } else {
                        $routeContent = $_routeContent['function'];
                    }

                    $noMatch = false;
                    break;
                }
            }

            // Si no hay coincidencias se muestra la vista por defecto del framework
            if ($noMatch) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    private static function notView() {
        if (Settings::getInstance()->inDebug()) {
            $ctx = new Context();
            $ctx->set('exception', 'Framework MVT');
            $ctx->set('details', 'Hurra ForlightPHP esta corriendo, ahora genera una vista.');

            // Le indico a la vista que haga render usando los templates del framework
            Settings::getInstance()->set('ForeverPHPTemplate', true);

            Response::make('foreverphp_exception', $ctx)->render();
        } else {
            // Si esta en produccion muestra un error 404
            self::redirectToError(404);
        }
    }

    private static function runFunction($function) {
        if (!is_string($function)) {
            // Ejecuta la funcion anonima
            $returnValue = call_user_func($function);

            /*
             * Valida si el valor de retorno de la funcion, es un objeto que
             * implemente ResponseInterface
             */
            if ($returnValue instanceof \ForeverPHP\Http\ResponseInterface) {
                $returnValue->make();
            }
        } else {
            self::notView();
        }
    }

    private static function addHeadersToResponse() {
        if (SessionManager::getInstance()->exists('headersInRedirect', 'redirect')) {
            $redirectPath = SessionManager::getInstance()->exists('redirectPath', 'redirect');
            $requestURI = $_SERVER['REQUEST_URI'];

            if ($redirectPath != $requestURI) {
                $headers = SessionManager::getInstance()->get('headersInRedirect', 'redirect');

                if ($headers != false) {
                    foreach ($headers as $key => $value) {
                        header($key . ': ' . $value);
                    }
                }
            } else {
                SessionManager::getInstance()->set('headersInRedirect', false, 'redirect');
            }
        }
    }

    /**
     * Ejecuta la ruta solicitada
     */
    public static function run() {
        // Obtiene la ruta actual
        $route = self::getRoute();

        /*
         * Establece como el manejador de excepciones no controladas a
         * ExceptionHandler
         */
        set_exception_handler("ExceptionManager::exceptionHandler");

        // Defino la ruta de los templates y estaticos del framework
        Setup::toDefine('FOREVERPHP_TEMPLATES_PATH', FOREVERPHP_ROOT . DS . 'static' . DS . 'templates' . DS);
        Setup::toDefine('FOREVERPHP_STATIC_PATH', basename(FOREVERPHP_ROOT) . DS . 'static' . DS);

        // Valida que tipo de ruta se solicito
        $routeContent = null;
        $appName = null;

        if (array_key_exists($route, self::$routes)) {
            $route = self::$routes[$route];

            if ($route['app'] != null) {
                $appName = $route['app'];
                $routeContent = $route;
            } else {
                $routeContent = $route['function'];
            }

            // Registro los parametros pasados en la solicitud
            Request::register();
        } else {
            // Se remueve el ultimo slash ya que rutas complejas no lo requieren
            //$route = self::remove_slash($route);
            if (!self::loadParamsRoute($route, $routeContent)) {
                $routeContent = null;
            }
        }

        // Agrega las cabeceras a la respuesta de existir
        static::addHeadersToResponse();

        if (is_array($routeContent)) {
            if ($appName == null) {
                $appName = $routeContent['app'];
            }

            // Primero se verifica que la aplicacion este agregada en la configuracion
            $app = App::getInstance();
            if ($app->exists($appName)) {
                $app->load($appName);
                $app->run($routeContent);
            } else {
                throw new AppException("La aplicación ($appName) a la que pertenece la vista no esta cargada en settings.php.");
            }
        } elseif (is_object($routeContent)) {
            self::runFunction($routeContent);
        } else {
            self::notView();
        }
    }
}
