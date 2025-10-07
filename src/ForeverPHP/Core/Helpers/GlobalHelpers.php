<?php

namespace ForeverPHP\Core\Helpers;

use ForeverPHP\Core\Settings;

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

    /**
     * Obtiene el lenguaje de la configuración.
     *
     * @return string
     */
    public static function getLanguage(): string
    {
        $availablesLangs = ['en', 'es'];
        $langFromSettings = Settings::getInstance()->exists('language')
            ? Settings::getInstance()->get('language')
            : 'en';

        // Lenguaje no disponible, se usa el lenguaje por defecto
        if (!in_array($langFromSettings, $availablesLangs)) {
            $langFromSettings = 'en';
        }

        return $langFromSettings;
    }
}
