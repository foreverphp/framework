<?php namespace ForeverPHP\Core\Helpers;

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
    public static function length($string)
    {
        return strlen($string);
    }

    /**
     * Convierte una cadena a minusculas.
     *
     * @param  string $string
     * @return string
     */
    public static function lower($string)
    {
        return mb_strtolower($string);
    }

    /**
     * Convierte una cadena a mayusculas.
     *
     * @param  string $string
     * @return string
     */
    public static function upper($string)
    {
        return mb_strtoupper($string);
    }
}
