<?php namespace ForeverPHP\Http;

/**
 * Permite obtener informacion del cliente
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class Host
{
    /**
     * Instancia singleton de Host.
     *
     * @var \ForeverPHP\Http\Host
     */
    private static $instance;

    public function __construct()
    {}

    /**
     * Obtiene o crea la instacia singleton de Host
     *
     * @return \ForeverPHP\Http\Host
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Obtiene la IP del huesped.
     *
     * @return string
     */
    public function getIP()
    {
        $ip = "UNKNOWN";

        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        }

        return $ip;
    }

    /**
     * Obtiene el nombre del huesped.
     *
     * @return string
     */
    public function getName()
    {
        return gethostbyaddr(getenv('REMOTE_ADDR'));
    }

    /**
     * Obtiene informacion del navegador del huesped.
     *
     * @param  integer $lenght
     * @return string
     */
    public function getUserAgent($lenght)
    {
        return substr(getenv('HTTP_USER_AGENT'), 0, $lenght);
    }
}
