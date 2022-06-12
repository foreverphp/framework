<?php namespace ForeverPHP\Core\Helpers;

/**
 * Funciones auxiliares globales.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class GlobalHelpers
{
    public static function env($name, $value = null)
    {
        if (!isset($_ENV[$name])) {
            if ($value != null) {
                putenv("$name=$value");
            }
        }

        $varEnv = getenv($name);

        if (strtolower($varEnv) === 'true' || strtolower($varEnv) === 'false') {
            $varEnv = $varEnv === 'true' ? true : false;
        } elseif (is_numeric($varEnv)) {
            $varEnv = (int) $varEnv;
        }

        return $varEnv;
    }
}
