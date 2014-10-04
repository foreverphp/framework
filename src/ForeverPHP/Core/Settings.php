<?php namespace ForeverPHP\Core;

/**
 * Permite administrar la configuracion del sistema y de las aplicaciones.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class Settings {
    private static $loaded = false;
    private static $settings = array();

    /*
     * Carga una vez el archivo de configuracion
     */
    private static function load() {
        if (!static::$loaded) {
            $path = APPS_ROOT . DS . 'settings.php';

            if (!file_exists($path)) {
                exit("El archivo de configuración settings.php no existe.");
            }

            static::$settings = require $path;

            if (!is_array(static::$settings)) {
                exit('El archivo de configuración no tiene el formato correcto.');
            }

            static::$loaded = true;
        }

        return true;
    }

    /**
     * Valida si existe el item de configuracion
     *
     * @param  string $name Nombre del item.
     * @return boolean
     */
    public static function exists($name) {
        self::load();

        if (array_key_exists($name, self::$settings)) {
            return true;
        }

        return false;
    }

    /**
     * Solo cambia la configuracion mientras el script este vivo
     *
     * @param string $name  Nombre del item.
     * @param mixed  $value Valor a asignar al item
     * @return boolean
     */
    public static function set($name, $value = null) {
        self::load();

        if ($value == null) {
            return false;
        }

        self::$settings[$name] = $value;
    }

    /**
     * Obtiene un item de configuracion
     *
     * @param  string $item Nombre del item a obtener.
     * @return mixed        Retorna el valor del item.
     */
    public static function get($name) {
        self::load();

        if (self::exists($name)) {
            return self::$settings[$name];
        }

        return false;
    }

    /**
     * Abreviación para ver si se está en Debug true.
     *
     * @return boolean
     */
    public static function inDebug() {
        self::load();

        return self::get('debug');
    }
}
