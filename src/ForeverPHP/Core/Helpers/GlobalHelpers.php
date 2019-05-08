<?php namespace ForeverPHP\Core\Helpers;

use ForeverPHP\Core\Facades\Storage;

/**
 * Funciones auxiliares globales.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class GlobalHelpers
{
    /*public static function env($nombre, $valor = null)
    {
        //
        // Cargar el .env aca esta mal ya que lo cargara por cada llamada a la función env,
        // ver donde cargarlo de manera optima. Podria ser con alguna funcion register o al similar.
        //
        if (Storage::exists(ROOT_PATH . DS . '.env')) {
            $dotenv = \Dotenv\Dotenv::create('../');
            $dotenv->load();
        }

        if (!isset($_ENV[$nombre])) {
            if ($valor != null) {
                putenv("$nombre=$valor");
            }
        }

        return getenv($nombre);
    }*/

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
            $varEnv = (int)$varEnv;
        }

        return $varEnv;
    }
}
