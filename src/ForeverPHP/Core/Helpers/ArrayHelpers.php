<?php

namespace ForeverPHP\Core\Helpers;

/**
 * Funciones auxiliares para colecciones.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class ArrayHelpers
{
    /**
     * Valida si el array dado es multidimensional.
     * @param array $array
     * @return bool
     */
    public static function isMultiArray(array $array): bool
    {
        return count(array_filter($array, 'is_array')) > 0;
    }

    /**
     * Convierte los elementos de un array a cadena de texto.
     * @param array $array
     * @return array
     */
    public static function convertToString(array $array): array
    {
        return array_map(function ($element) {
            return (string)$element;
        }, $array);
    }
}
