<?php namespace ForeverPHP\Session;

use ForeverPHP\Core\Settings;

/**
 * Controla y gestiona las sessiones en el framework.
 *
 * @since       Version 0.1.0
 */
class SessionManager {
    /**
     * Contiene la instancia unica del objeto.
     *
     * @var \ForeverPHP\Session\SessionManager
     */
    private static $instance;

    private function __construct() {}

    /**
     * Obtiene o crea la instancia unica del administrador de sesiones.
     *
     * @return \ForeverPHP\Session\SessionManager
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function isSessionStarted()
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

    private function sessionStart() {
        session_name(Settings::get('sessionName'));

        if (!$this->isSessionStarted()) {
            session_start();
        }
    }

    public function exists($key, $section = 'main') {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            if (isset($_SESSION[$section][$key])) {
                return true;
            }
        }

        return false;
    }

    public function existsSection($section) {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            if (isset($_SESSION[$section])) {
                return true;
            }
        }

        return false;
    }

    public function set($key, $value, $section = 'main') {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            // Agrego la llave a la sesion
            $_SESSION[$section][$key] = $value;
        }
    }

    public function get($key, $section = 'main') {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            if ($this->exists($key, $section)) {
                // Retorna el valor de la llave
                return $_SESSION[$section][$key];
            }
        }

        return null;
    }

    public function remove($key, $section = 'main') {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            if ($this->exists($key, $section)) {
                unset($_SESSION[$section][$key]);
            }
        }
    }

    public function removeSection($section) {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            if ($this->existsSection($section)) {
                unset($_SESSION[$section]);
            }
        }
    }

    public function regenerate($deleteOldSession = false) {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
            session_regenerate_id($deleteOldSession);
            //self::$id = session_id();
        }
    }

    public function destroy() {
        $this->sessionStart();

        if ($this->isSessionStarted()) {
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
