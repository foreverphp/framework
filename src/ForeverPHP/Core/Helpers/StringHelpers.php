<?php namespace ForeverPHP\Core\Helpers;

/**
 * Funciones auxiliares para cadenas.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class StringHelpers {
    /**
     * [lower description]
     *
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public static function lower($string) {
        return mb_strtolower($string);
    }
}