<?php

namespace ForeverPHP\Session;

use ForeverPHP\Core\Settings;

/**
 * Gestiona las sesiones en el framework ForeverPHP.
 *
 * Esta clase implementa el patrón Singleton para asegurar
 * que solo exista una instancia del manejador de sesiones.
 *
 * @since 0.4.0
 */
class SessionManager
{
    /**
     * Instancia única de la clase SessionManager.
     *
     * @var \ForeverPHP\Session\SessionManager
     */
    private static ?self $instance = null;

    /**
     * Constructor privado para evitar instanciación directa.
     */
    private function __construct()
    {
    }

    /**
     * Obtiene o crea la instancia singleton de SessionManager.
     *
     * @return \ForeverPHP\Session\SessionManager
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Verifica si la sesión está iniciada.
     *
     * @return bool
     */
    private function isSessionStarted(): bool
    {
        if (php_sapi_name() === 'cli') {
            return false;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE;
        }
        return session_id() !== '';
    }

    /**
     * Inicia la sesión si aún no está activa.
     *
     * @return void
     */
    private function sessionStart(): void
    {
        if (!$this->isSessionStarted()) {
            session_name(Settings::getInstance()->get('sessionName'));
            session_start();
        }
    }

    /**
     * Asegura que la sesión esté iniciada y retorna el estado.
     *
     * @return bool True si la sesión está activa tras el intento de inicio.
     */
    private function ensureStarted(): bool
    {
        $this->sessionStart();
        return $this->isSessionStarted();
    }

    /**
     * Verifica si existe una clave dentro de una sección de sesión.
     *
     * @param string $key      Clave de sesión.
     * @param string $section  Sección de la sesión (por defecto 'main').
     * @return bool
     */
    public function exists(string $key, string $section = 'main'): bool
    {
        return $this->ensureStarted() && isset($_SESSION[$section][$key]);
    }

    /**
     * Valida si todas las claves especificadas existen en un mismo namespace de sesión.
     *
     * @param array $keys Lista de claves a validar.
     * @param string $section Namespace de sesión (por defecto 'main').
     * @return bool Devuelve true si todas existen, false si alguna no existe.
     */
    public function existsAll(array $keys, string $section = 'main'): bool
    {
        if (!$this->ensureStarted()) {
            return false;
        }

        foreach ($keys as $key) {
            if (!isset($_SESSION[$section][$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica si existe una sección de sesión.
     *
     * @param string $section Nombre de la sección.
     * @return bool
     */
    public function existsSection(string $section): bool
    {
        return $this->ensureStarted() && isset($_SESSION[$section]);
    }

    /**
     * Almacena un valor en la sesión.
     *
     * @param string $key      Clave de la variable.
     * @param mixed  $value    Valor a almacenar.
     * @param string $section  Sección de la sesión (por defecto 'main').
     * @return void
     */
    public function set(string $key, mixed $value, string $section = 'main'): void
    {
        if ($this->ensureStarted()) {
            if (!isset($_SESSION[$section]) || !is_array($_SESSION[$section])) {
                $_SESSION[$section] = [];
            }
            $_SESSION[$section][$key] = $value;
        }
    }

    /**
     * Obtiene un valor de la sesión.
     *
     * @param string $key      Clave de la variable.
     * @param string $section  Sección de la sesión (por defecto 'main').
     * @return mixed|null
     */
    public function get(string $key, string $section = 'main'): mixed
    {
        if ($this->ensureStarted() && isset($_SESSION[$section][$key])) {
            return $_SESSION[$section][$key];
        }

        return null;
    }

    /**
     * Elimina una variable de la sesión.
     *
     * @param string $key     Clave a eliminar.
     * @param string $section Sección donde se encuentra (por defecto 'main').
     * @return void
     */
    public function remove(string $key, string $section = 'main'): void
    {
        if ($this->ensureStarted() && isset($_SESSION[$section][$key])) {
            unset($_SESSION[$section][$key]);
        }
    }

    /**
     * Elimina una sección completa de la sesión.
     *
     * @param string $section Nombre de la sección.
     * @return void
     */
    public function removeSection(string $section): void
    {
        if ($this->ensureStarted() && isset($_SESSION[$section])) {
            unset($_SESSION[$section]);
        }
    }

    /**
     * Regenera el ID de sesión.
     *
     * @param bool $deleteOldSession Si se debe eliminar la sesión anterior (por defecto false).
     * @return void
     */
    public function regenerate(bool $deleteOldSession = false): void
    {
        if ($this->ensureStarted()) {
            session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Destruye completamente la sesión.
     *
     * Limpia todas las variables, elimina la cookie de sesión
     * y destruye la sesión activa.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (!$this->ensureStarted()) {
            return;
        }

        // Vaciar variables de sesión
        $_SESSION = [];

        // Eliminar cookie de sesión correctamente
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            $params['secure'] ?? false,
            $params['httponly'] ?? false
        );

        // Destruir sesión
        session_destroy();
    }
}
