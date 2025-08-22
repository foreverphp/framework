<?php

/**
 * funciones helpers ejemplos
 *
 * snake_case
 * camel_case
 *
 * Nota este archivo se debe cargar al iniciar el framework
 */

if (!function_exists('render_to_response')) {
    /**
     * [array_add description]
     *
     * @param  [type] $array [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    function render_to_responce($template, $context = null)
    {
        //return ArrayHelpers::arrayAdd($array, $key, $value);
    }
}

if (!function_exists('render_to_json')) {
    function render_to_json($data)
    {
        //
    }
}
