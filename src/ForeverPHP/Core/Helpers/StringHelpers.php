<?php

namespace ForeverPHP\Core\Helpers;

/**
 * Funciones auxiliares para cadenas.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class StringHelpers
{
    /**
     * Devuelve el largo de la cadena.
     *
     * @param  string $string
     * @return string
     */
    public static function length(string $string)
    {
        return strlen($string);
    }

    /**
     * Convierte una cadena a minusculas.
     *
     * @param  string $string
     * @return string
     */
    public static function lower(string $string)
    {
        return mb_strtolower($string);
    }

    /**
     * Convierte una cadena a mayusculas.
     *
     * @param  string $string
     * @return string
     */
    public static function upper(string $string)
    {
        return mb_strtoupper($string);
    }

    /**
     * Une múltiples valores en un string sin separador.
     * Los valores no-string se convierten automáticamente a string.
     *
     * @param mixed ...$lines Los valores a unir (se convierten a string automáticamente)
     * @return string
     */
    public static function joinLines(...$lines): string
    {
        return implode('', array_map('strval', $lines));
    }

    /**
     * Une múltiples valores usando un separador.
     *
     * @param string $glue El separador entre cada valor
     * @param mixed ...$lines Los valores a unir
     * @return string
     */
    public static function joinLinesWith(string $glue, ...$lines): string
    {
        return implode($glue, array_map('strval', $lines));
    }
}
