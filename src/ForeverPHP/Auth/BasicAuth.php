<?php namespace ForeverPHP\Auth;

/**
 * Autentificacion basica HTTP.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class BasicAuth
{
    private static $_username = null;
    private static $_password = null;

    public static function sendHeader($realm, $message)
    {
        header('WWW-Authenticate: Basic realm="' . $message . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo $message . "\n";
        exit;
    }

    public static function validate()
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            self::$_username = $_SERVER['PHP_AUTH_USER'];
            self::$_password = $_SERVER['PHP_AUTH_PW'];

            return true;
        }

        return false;
    }

    public static function username()
    {
        return self::$_username;
    }

    public static function password()
    {
        return self::$_password;
    }
}
