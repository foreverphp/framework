<?php namespace ForeverPHP\Routing;

use ForeverPHP\Core\{App, Settings, Setup};
use ForeverPHP\Core\Exceptions\{AppException, RouterException};
use ForeverPHP\Core\Facades\{Redirect, Request, Storage};
use ForeverPHP\Http\Response;
use ForeverPHP\Session\SessionManager;
use ForeverPHP\View\{Context, View};

/**
 * Almacena todas las rutas en una matriz para luego ejecutar la ruta
 * solicitada.
 *
 * @author      Daniel Nuñez S. <dnunez@pointers.cl>
 * @since       Version 1.0.0
 */
class Router
{
    private $uriBase = '/';
    private $routes = array();
    private $complexRoutes = array();
    private $nameForRoute = null;

    private $gets = array();
    private $posts = array();

    /**
     * Contiene la instancia singleton de Router.
     *
     * @var \ForeverPHP\Routing\Router
     */
    private static $instance;

    public function Router()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de Router.
     *
     * @return \ForeverPHP\Routing\Router
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function addSlash(string $route)
    {
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

    private function removeSlash(string $route)
    {
        $url = $route;

        if ($url[strlen($url) - 1] == '/') {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    private function getUriBase()
    {
        $this->uriBase = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    }

    private function parseRoute(string $route, array &$paramsUrl)
    {
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
            $newRoute = $this->addSlash($newRoute);
        }

        return $newRoute;
    }

    /*
     * ABSOLETA: Se eliminara esta version ya que ahora habra un directorio con rutas.
     */
    /**
     * Carga las rutas propias de una aplicación.
     *
     * @param  string $appName
     */
    public function fromApp(string $appName)
    {
        require_once APPS_ROOT . DS . $appName . DS . 'approutes.php';
    }

    public function get()
    {
        //
    }

    public function post()
    {
        //
    }

    /*
     * ESTA FUNCION QUEDARA OBSOLETA SIENDO REEMPLAZADA POR get, post, ENTRE OTRAS, ESTA FUNCIONA AUN NO SE ELIMINARA
     * POR TEMAS DE COMPATIBILIDAD PERO SE MODIFICARA, Y ESTA SEGUN EL METODO DE LLAMADA DE LA RUTA LLAMARA A LA FUNCION
     * get, post, etc SEGUN SEA NECESARIO
     */
    public function add(string $route, string $view, $middlewares = null)
    {
        $app = null;        // Aplicacion donde esta la vista
        $v = null;          // Vista a buscar
        $method = 'run';  // Metodo por defecto a ejecutar

        // Se valida si es una vista a ejecutar o una funcion anonima
        if (is_string($view)) {
            if (!strpos($view, '@')) {
                throw new RouterException("Revise la ruta ($route) al parecer la ruta a la vista no esta correctamente escrita.");
            }

            // Dividir la vista en app, vista y funcion si es que esta definida
            $view = explode('@', $view);
            $app = $view[0];
            $v = $view[1];

            // Valida si la vista trae un metodo a ejecutar
            if (count($view) == 3) {
                $method = $view[2];
            }
        } elseif (is_array($view)) {
            /*
             * Definicion de una vista con matriz.
             *
             * $view = array(
             *     'name' => 'nombre_vista',
             *     'app.vista' o 'view' => 'app.view.function' o function() {}
             * );
             */

            // Si el numero de items de la matriz es menor a dos no es valido
            if (count($view) < 2) {
                throw new RouterException('Definición de vista invalida.');
            }

            if (!array_key_exists('name', $view)) {
                throw new RouterException("No existe el atributo 'name' de la vista en la matriz.");
            }

            /*
             * Almacena temporalmente el nombre de la ruta para que al volver a
             * llamar a add se agregue el nombre a la ruta.
             */
            $this->nameForRoute = $view['name'];

            // Vuelve a llamar a add para agregar la ruta
            return $this->add($route, $view[0], $middlewares);
        } else {
            $method = $view;
        }

        // Se valida si la ruta trae parametros por ruta
        $paramsUrl = array();
        $route = $this->parseRoute($route, $paramsUrl);

        // Matriz con el contenido de la ruta
        $routeContent = array(
            'app' => $app,
            'view' => $v,
            'method' => $method,
            'paramsUrl' => $paramsUrl,
            'name' => $this->nameForRoute,
            'middlewares' => $middlewares
        );

        // Valida si es ruta normal o compleja
        if (count($paramsUrl) == 0) {
            $this->routes[$route] = $routeContent;
        } else {
            $this->complexRoutes[$route] = $routeContent;
        }
    }



    /**
     * Obtiene la ruta actual
     *
     * @return string
     */
    public function getRoute(): string
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Extraigo la URI base si esta es diferente a /
        if ($this->uriBase != '/') {
            $uri = str_replace($this->uriBase, '/', $uri);
        }

        // Busco el caracter ? por si se pasaron parametros por GET
        $pos = strpos($uri, '?');

        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Agrega un slash a uri para evitar error en la busqueda de ultimo slash
        //$uri = $this->addSlash($uri);

        // Retorno la ruta correcta
        return $uri;
    }

    /**
     * Obtiene el nombre de la ruta actual si es que se le definio uno.
     *
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->nameForRoute;
    }

    private function loadParamsRoute(string &$route, string &$routeContent)
    {
        $noMatch = true; // Indica si hay o no coincidencias de ruta

        if (count($this->complexRoutes) > 0) {
            foreach ($this->complexRoutes as $complexRoute => $_routeContent) {
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
                        if ($matches[$i] != null && $matches[$i] != 'undefined') {
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

                    /*
                     * Devuelvo la ruta compleja para obtener su nombre y
                     * decoradores.
                     */
                    $route = $_routeContent;

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

    private function notView()
    {
        if (Settings::getInstance()->inDebug()) {
            Context::getInstance()->set('exception', 'Framework MVT');
            Context::getInstance()->set('details', 'Hurra ForeverPHP esta corriendo, ahora genera una vista.');

            // Le indico a la vista que haga render usando los templates del framework
            Settings::getInstance()->set('ForeverPHPTemplate', true);

            $response = new Response();
            $response->render('exception')->make();
        } else {
            // Si esta en produccion muestra un error 404
            //(new Redirect)->error(404);
            Redirect::error(404);
        }
    }

    private function runFunction($function)
    {
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
            $this->notView();
        }
    }

    private function setHeadersToResponse()
    {
        if (SessionManager::getInstance()->exists('headersInRedirect', 'redirect')) {
            $redirectPath = SessionManager::getInstance()->get('redirectPath', 'redirect');
            $requestURI = $_SERVER['REQUEST_URI'];

            if ($redirectPath == $requestURI) {
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
    public function run()
    {
        // Obtiene la Url base
        $this->getUriBase();

        // Obtiene la ruta actual
        $route = $this->addSlash($this->getRoute());

        // Valida que tipo de ruta se solicito
        $routeContent = null;
        $appName = null;

        if (array_key_exists($route, $this->routes)) {
            $route = $this->routes[$route];

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
            if (!$this->loadParamsRoute($route, $routeContent)) {
                $routeContent = null;
            }
        }

        // Agrega las cabeceras a la respuesta de existir
        $this->setHeadersToResponse();

        /*
         * Almacena el nombre de la ruta actual si es que lo tiene, dependiendo
         * si es una ruta normal o compleja y si es funcion o vista.
         */
        if (is_array($route)) {
            $this->nameForRoute = $route['name'];
        } elseif (is_array($routeContent)) {
            $this->nameForRoute = $routeContent['name'];
        }

        /*
         * NOTA: Los decoradores solo pueden ser utilizados en rutas con vistas
         *       no en rutas con funciones anonimas.
         */

        if (is_array($routeContent)) {
            if ($appName == null) {
                $appName = $routeContent['app'];
            }

            // Primero se verifica que la aplicacion este agregada en la configuracion
            $app = App::getInstance();
            if ($app->exists($appName)) {
                $app->load($appName);

                // Cargo el autoload.php, archivo opcional
                $autoloadPath = APPS_ROOT . DS . 'autoload.php';

                if (Storage::exists($autoloadPath)) {
                    include_once $autoloadPath;
                }

                // Ejecuta los decoradores de la ruta si es que hay
                $middlewares = $routeContent['middlewares'];

                if (!is_null($middlewares)) {
                    foreach ($middlewares as $middleware) {
                        $returnValue = $app->getMiddleware($middleware);

                        // Valida si el decorador retorna un Response
                        if ($returnValue instanceof \ForeverPHP\Http\ResponseInterface) {
                            $app->run($returnValue);
                        }
                    }
                }

                $app->run($routeContent);
            } else {
                throw new AppException("La aplicación ($appName) a la que pertenece la vista no esta " .
                    "cargada en settings.php.");
            }
        } elseif (is_callable($routeContent)) {
            $this->runFunction($routeContent);
        } else {
            $this->notView();
        }
    }
}
