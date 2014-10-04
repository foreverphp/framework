<?php namespace ForeverPHP\Routing;

class Redirect {

    public static function to($url) {
        header('Location: ' . $url);
    }

    public static function toError($errno) {
        // Debo agregar el texto correcto al encabezado
        // no Forbidden
        header("HTTP/1.0 $errno Forbidden", true, $errno);
        exit();
    }
}