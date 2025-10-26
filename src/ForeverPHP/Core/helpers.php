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

use ForeverPHP\Core\Helpers\GlobalHelpers;
use ForeverPHP\Core\Helpers\ArrayHelpers;
use ForeverPHP\Core\Helpers\StringHelpers;

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

/**
 * Obtiene un secret desde el archivo de secrets.
 *
 * @param string $name
 * @return string
 */
if (!function_exists('secret')) {
    function secret(string $name): ?string
    {
        static $cache = null;

        if ($cache === null) {
            $key = base64_decode(
                file_get_contents(
                    safe_const('ROOT_PATH') . safe_const('DS') . '.secrets' . safe_const('DS') . 'master-password.key'
                )
            );
            $data = json_decode(
                file_get_contents(
                    safe_const('ROOT_PATH') . safe_const('DS') . '.secrets' . safe_const('DS') . 'secrets.json'
                ),
                true
            );

            $cache = [];
            foreach ($data as $k => $v) {
                $bin = base64_decode($v);
                $nonce = substr($bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $cipher = substr($bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $cache[$k] = sodium_crypto_secretbox_open($cipher, $nonce, $key);
            }
        }

        return $cache[$name] ?? null;
    }
}
