<?php namespace ForeverPHP\Core;

/**
 * Controla y gestiona las cookies en el framework.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class Cookie
{
    /**
     * Almacena los minutos de expiracion de las cookies.
     *
     * @var array
     */
    private $cookiesExpires = array();

    /**
     * Contiene la instancia singleton de Cookie.
     *
     * @var \ForeverPHP\Core\Cookie
     */
    private static $instance;

    public function __construct()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de Cookie.
     *
     * @return \ForeverPHP\Core\Cookie
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Verifica que exista la cookie.
     *
     * @param  string $name Nombre de la cookie.
     * @return boolean      Devuelve true si existe la cookie y false si no.
     */
    public function exists($name)
    {
        if (isset($_COOKIE[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Crea una nueva cookie.
     *
     * @param string  $name     Nombre de la cookie.
     * @param string  $value    Valor a almacenar en la cookie, queda en base64.
     * @param integer $expire   Expiracion de la cookie en minutos, si esta en -1 durara 1 año.
     * @param string  $path     La ruta dentro del servidor en la que la cookie estará disponible.
     * @param string  $domain   El dominio para el cual la cookie está disponible.
     * @param boolean $secure   Indica que la cookie sólo debiera transmitirse por una conexión segura HTTPS
     *                          desde el cliente.
     * @param boolean $httponly Cuando es TRUE la cookie será accesible sólo a través del protocolo HTTP.
     */
    public function set($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        /**
         * La cookie expira al cerrar el navegador por defecto, pero si
         * en $expire se pasa un -1 la cookie tendra una duracion de 1 año.
         */
        if ($expire === -1) {
            $expire = time() + 3600 * 24 * 365;
        } else {
            $expire *= 60;
        }

        // Convierto el valor a base64
        $value = base64_encode($value);

        // Crea la cookie
        if ($path != null) {
            if ($domain != null) {
                if ($secure) {
                    if ($httpOnly) {
                        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
                    } else {
                        setcookie($name, $value, $expire, $path, $domain, $secure);
                    }
                } else {
                    setcookie($name, $value, $expire, $path, $domain);
                }
            } else {
                setcookie($name, $value, $expire, $path);
            }
        } else {
            setcookie($name, $value, $expire);
        }
    }

    /**
     * Crea una nueva cookie, por un año.
     *
     * @param string  $name     Nombre de la cookie.
     * @param string  $value    Valor a almacenar en la cookie, queda en base64.
     * @param string  $path     La ruta dentro del servidor en la que la cookie estará disponible.
     * @param string  $domain   El dominio para el cual la cookie está disponible.
     * @param boolean $secure   Indica que la cookie sólo debiera transmitirse por una conexión segura HTTPS
     *                          desde el cliente.
     * @param boolean $httponly Cuando es TRUE la cookie será accesible sólo a través del protocolo HTTP.
     */
    public function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        $this->set($name, $value, -1, $path, $domain, $secure, $httpOnly);
    }

    // Implementar en versiones futuras
    /*public static function set_array($name, $array, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false) {
    if (is_array($array)) {
    // Recorro la matriz y genero tantas cookies como elementos tenga la matriz
    foreach ($array as $key => $value) {
    self::set($name . '[' . $key . ']', $value, $expire, $path, $domain, $secure, $httponly);
    }
    }
    }*/

    /**
     * Obtiene una cookie.
     *
     * @param  string $name Nombre de la cookie.
     * @return boolean      De encontrarse la cookie se retorna si no, se retornara false.
     */
    public function get($name)
    {
        if ($this->exists($name)) {
            // Retorna el valor de la cookie
            return base64_decode($_COOKIE[$name]);
        }

        return false;
    }

    /**
     * Elimina una cookie.
     *
     * @param string  $name     Nombre de la cookie.
     * @param string  $path     La ruta dentro del servidor en la que la cookie estará disponible.
     * @param string  $domain   El dominio para el cual la cookie está disponible.
     * @param boolean $secure   Indica que la cookie sólo debiera transmitirse por una conexión segura HTTPS
     *                          desde el cliente.
     * @param boolean $httponly Cuando es TRUE la cookie será accesible sólo a través del protocolo HTTP.
     */
    public function remove($name, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        if ($this->exists($name)) {
            $expire = time() - (3600 * 24 * 365);

            $this->set($name, '', $expire, $path, $domain, $secure, $httpOnly);
        }
    }
}
