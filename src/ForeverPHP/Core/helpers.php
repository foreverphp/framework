<?php
/**
 * funciones helpers ejemplos
 *
 * snake_case
 * camel_case
 *
 * Nota este archivo se debe cargar al iniciar el framework
 */

use ForeverPHP\Core\Helpers\ArrayHelpers;
use ForeverPHP\Core\Helpers\StringHelpers;

if (!function_exists('array_add')) {
    /**
     * [array_add description]
     *
     * @param  [type] $array [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    function array_add($array, $key, $value)
    {
        return ArrayHelpers::arrayAdd($array, $key, $value);
    }
}

if (!function_exists('camel_case')) {
    function camel_case()
    {
        //
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
