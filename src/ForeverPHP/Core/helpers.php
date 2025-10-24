<?php

/**
 * funciones helpers ejemplos
 *
 * snake_case
 * camel_case
 *
 * sacar algunas ideas de laravel
 *
 * Nota este archivo se debe cargar al iniciar el framework
 */

use ForeverPHP\Core\Facades\Storage;
use ForeverPHP\Core\Helpers\GlobalHelpers;
use ForeverPHP\Core\Helpers\ArrayHelpers;
use ForeverPHP\Core\Helpers\RouteHelpers;
use ForeverPHP\Core\Helpers\StringHelpers;

// Carga las variables de entorno desde el archivo .env
// TODO: Mover a bootstrap.php
if (Storage::exists(ROOT_PATH . DS . '.env')) {
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(ROOT_PATH);
        $dotenv->load();
    }
}

if (!function_exists('is_multi_array')) {
    /**
     * Valida si el array dado es multidimensional.
     * @param array $array
     * @return bool
     */
    function is_multi_array(array $array)
    {
        return ArrayHelpers::isMultiArray($array);
    }
}

if (!function_exists('array_convert_to_string')) {
    /**
     * Convierte los elementos de un array a cadena de texto.
     * @param array $array
     * @return array
     */
    function array_convert_to_string(array $array)
    {
        return ArrayHelpers::convertToString($array);
    }
}

if (!function_exists('camel_case')) {
    function camel_case()
    {
        //
    }
}

if (!function_exists('env')) {
    function env($nombre, $valor = null)
    {
        return GlobalHelpers::env($nombre, $valor);
    }
}

if (!function_exists('length')) {
    function length($string)
    {
        return StringHelpers::length($string);
    }
}

if (!function_exists('lower')) {
    function lower($string)
    {
        return StringHelpers::lower($string);
    }
}

if (!function_exists('snake_case')) {
    function snake_case()
    {
        //
    }
}

if (!function_exists('upper')) {
    function upper($string)
    {
        return StringHelpers::upper($string);
    }
}

/**
 * Valida si una constante esta definida
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
if (!function_exists('safe_const')) {
    function safe_const(string $name, mixed $default = null): mixed
    {
        return defined($name) ? constant($name) : $default;
    }
}
