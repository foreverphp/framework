<?php namespace ForeverPHP\Session;

use ForeverPHP\Core\Settings;

/**
 * Controla y gestiona las sessiones en el framework.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class Session {
    private static function isSessionStarted()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return session_id() === '' ? false : true;
            }
        }

        return false;
    }

    private static function sessionStart() {
        session_name(Settings::get('sessionName'));

        if (!self::isSessionStarted()) {
            session_start();
        }
    }

    public static function exists($key, $section = 'main') {
        self::sessionStart();

        if (self::isSessionStarted()) {
            if (isset($_SESSION[$section][$key])) {
                return true;
            }
        }

        return false;
    }

    public static function existsSection($section) {
        self::sessionStart();

        if (self::isSessionStarted()) {
            if (isset($_SESSION[$section])) {
                return true;
            }
        }

        return false;
    }

    public static function set($key, $value, $section = 'main') {
        self::sessionStart();

        if (self::isSessionStarted()) {
            // Agrego la llave a la sesion
            $_SESSION[$section][$key] = $value;
        }
    }

    public static function get($key, $section = 'main') {
        self::sessionStart();

        if (self::isSessionStarted()) {
            if (self::exists($key, $section)) {
                // Retorna el valor de la llave
                return $_SESSION[$section][$key];
            }
        }

        return null;
    }

    public static function remove($key, $section = 'main') {
        self::sessionStart();

        if (self::isSessionStarted()) {
            if (self::exists($key, $section)) {
                unset($_SESSION[$section][$key]);
            }
        }
    }

    public static function removeSection($section) {
        self::sessionStart();

        if (self::isSessionStarted()) {
            if (self::existsSection($section)) {
                unset($_SESSION[$section]);
            }
        }
    }

    public static function regenerate($deleteOldSession = false) {
        self::sessionStart();

        if (self::isSessionStarted()) {
            session_regenerate_id($deleteOldSession);
            //self::$id = session_id();
        }
    }

    /**
     * Funcion necesaria para trabajar usuarios, al iniciar sesion simpre debe
     * ser llamada y se le debe entregar una matriz con los datos de inicio de
     * sesion que se deseen
     *
     * @param array $data
     */
    /*public static function logon($data) {
        foreach ($data as $key => $value) {
            self::set($key, $value, 'logon');
        }
    }

    public static function is_logon() {
        return self::exists_section('logon');
    }*/

    public static function destroy() {
        self::sessionStart();

        if (self::isSessionStarted()) {
            // Destruir todas las variables de sesión
            $_SESSION = array();

            /*
             * Si se desea destruir la sesión completamente, borre también la cookie de sesión.
             * Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
             */
            $paramsCookie = session_get_cookie_params();
            setcookie(session_name(), 0, 1, $paramsCookie['path']);

            // Finalmente, destruir la sesión
            session_destroy();
        }
    }
}
