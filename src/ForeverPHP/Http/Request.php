<?php namespace ForeverPHP\Http;

use ForeverPHP\Core\Settings;

/**
 * Contiene parametros he informacion del request.
 *
 * @since       Version 0.1.0
 */
class Request {

    private static $registered = false;

    private static $files = null;

    private static $method = 'get';

    private static $params = null;

    private static function loadRequest() {
        $requestParams = null;

        if (self::$params == null) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            self::$files = array();
            self::$params = array();

            if ($requestMethod == 'GET') {
                self::$method = 'get';
                $requestParams = $_GET;
            } elseif ($requestMethod == 'POST') {
                self::$method = 'post';
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
                    self::$method = 'put';
                } else {
                    self::$method = 'delete';
                }
            }

            foreach($requestParams as $name => $value) {
                if ($name == 'csrfToken') {
                    // Almaceno el token CSRF para luego validarlo
                    Settings::getInstance()->set($name, $value);
                } else {
                    self::$params[$name] = $value;
                }
            }
        }
    }

    public static function register($params = null) {
        if (!static::$registered) {
            if ($params == null) {
                self::loadRequest();
            } else {
                // Parametros pasados por Url
                // Ejemplo: posts/post/12 (posts/post/{id})
                self::$params = $params;
            }

            static::$registered = true;
        }
    }

    public static function path() {
        // retorna la uri
    }

    public static function url() {
        // retorna la url del request
    }

    public static function segment($number) {
        // devuelve el segmento de url indicado
    }

    public static function is($path) {
        // valida si se esta en el path
    }

    public static function header($name) {
        // devuelve el elemento del header ejemplo 'Content-Type'
    }

    public static function server($var) {
        // retorna valores de $_SERVER
    }

    public static function host() {
        return Host::getInstance();
    }

    public static function method() {
        return self::$_method;
    }

    public static function isMethod($method) {
        if (strtolower($method) === self::$method) {
            return true;
        }

        return false;
    }

    public static function secure() {
        // devuelve si esta en https o no
    }

    public static function ajax() {
        // devuelve si esta en ajax o no
    }

    public static function isJson() {
        // devuelve si el request content-type es de tipo json
    }

    public static function wantsJson() {
        // devuelve si la solicitud esta pidiendo json o no
    }

    public static function format($format) {
        /*
        Comprobación del formato de respuesta de la petición de

        El método Request :: format devuelve el formato de respuesta solicitada basándose en la cabecera HTTP Accept header:
         */
    }

    public static function exists($name) {
        if (!is_null(self::$params)) {
            if (array_key_exists($name, self::$params)) {
                return true;
            }
        }

        return false;
    }

    public static function get($name) {
        if (self::exists($name)) {
            return self::$params[$name];
        }

        return false;
    }

    public static function all() {
        return self::$params;
    }

    public static function hasFile($name) {
        // indica si el parametro pasado por nombre es de tipo file
    }

    public static function file($name) {
        // retorna un parametro de tipo archivo
    }
}
