<?php namespace ForeverPHP\Core\Helpers;

/**
 * Funciones auxiliares globales.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class GlobalHelpers
{
    public static function env($nombre, $valor = null)
    {
        if (!isset($_ENV[$nombre])) {
            if ($valor != null) {
                putenv("$nombre=$valor");
            }
        }

        return getenv($nombre);
    }
}
