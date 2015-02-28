<?php namespace ForeverPHP\Http;

use ForeverPHP\Core\Settings;
use ForeverPHP\Http\RequestFile;

/**
 * Contiene parametros he informacion del request.
 *
 * @since       Version 0.1.0
 */
class Request {

    private $registered = false;

    private $files = null;

    private $method = 'get';

    private $params = null;

    /**
     * Contiene la instancia singleton de Request.
     *
     * @var \ForeverPHP\Http\Request
     */
    private static $instance;

    public function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de Request.
     *
     * @return \ForeverPHP\Http\Request
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function loadRequest() {
        $requestParams = null;

        if ($this->params == null) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $this->files = array();
            $this->params = array();

            if ($requestMethod == 'GET') {
                $this->method = 'get';
                $requestParams = $_GET;
            } elseif ($requestMethod == 'POST') {
                $this->method = 'post';
                $requestParams = $_POST;
            } elseif ($requestMethod == 'PUT' || $requestMethod == 'DELETE') {
                /*
                 * PHP no tiene un método propiamente dicho para leer una petición PUT o DELETE,
                 * por lo que se usa un "truco".
                 * Leer el stream de entrada file_get_contents("php://input") que transfiere un
                 * fichero a una cadena.
                 * Con ello obtenemos una cadena de pares clave valor de variables
                 * (variable1=dato1&variable2=data2...) que evidentemente tendremos que
                 * transformarla a un array asociativo.
                 */
                $requestContent = file_get_contents("php://input");
                parse_str($requestContent, $requestParams);

                if ($requestMethod == 'PUT') {
                    $this->method = 'put';
                } else {
                    $this->method = 'delete';
                }
            }

            // Verifica si hay archivos enviados
            if (count($_FILES) != 0) {
                foreach ($_FILES as $name => $value) {
                    $this->files[$name] = new RequestFile($value);
                }
            }

            foreach($requestParams as $name => $value) {
                if ($name == 'csrfToken') {
                    // Almaceno el token CSRF para luego validarlo
                    Settings::getInstance()->set($name, $value);
                } else {
                    $this->params[$name] = $value;
                }
            }
        }
    }

    public function register($params = null) {
        if (!$this->registered) {
            if ($params == null) {
                $this->loadRequest();
            } else {
                // Parametros pasados por Url
                // Ejemplo: posts/post/12 (posts/post/{id})
                $this->params = $params;
            }

            $this->registered = true;
        }
    }

    public function path() {
        // retorna la uri
    }

    public function url() {
        // retorna la url del request
    }

    public function segment($number) {
        // devuelve el segmento de url indicado
    }

    public function is($path) {
        // valida si se esta en el path
    }

    public function header($name) {
        // devuelve el elemento del header ejemplo 'Content-Type'
    }

    public function server($var) {
        // retorna valores de $_SERVER
    }

    public function host() {
        return Host::getInstance();
    }

    public function method() {
        return self::$_method;
    }

    public function isMethod($method) {
        if (strtolower($method) === self::$method) {
            return true;
        }

        return false;
    }

    public function secure() {
        // devuelve si esta en https o no
    }

    public function ajax() {
        // devuelve si esta en ajax o no
    }

    public function isJson() {
        // devuelve si el request content-type es de tipo json
    }

    public function wantsJson() {
        // devuelve si la solicitud esta pidiendo json o no
    }

    public function format($format) {
        /*
        Comprobación del formato de respuesta de la petición de

        El método Request :: format devuelve el formato de respuesta solicitada basándose en la cabecera HTTP Accept header:
         */
    }

    public function exists($name) {
        if (!is_null($this->params)) {
            if (array_key_exists($name, $this->params)) {
                return true;
            }
        }

        return false;
    }

    public function get($name) {
        if ($this->exists($name)) {
            return $this->params[$name];
        }

        return false;
    }

    public function all() {
        return $this->params;
    }

    public function hasFile($name) {
        // indica si el parametro pasado por nombre es de tipo file
    }

    public function file($name) {
        // retorna un parametro de tipo archivo
    }
}
