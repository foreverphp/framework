<?php namespace ForeverPHP\Core;

/**
 * Realiza la carga de los alias de clases.
 * Los alias de carga se definen en el archivo de configuración
 * 'settings.php'
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class AliasLoader {
    /**
     * Matriz que contiene los alias de clase.
     *
     * @var array
     */
    private $aliases;

    /**
     * Valida si ya se hizo el registro de alias.
     *
     * @var boolean
     */
    private $registered = false;

    /**
     * Instancia singleton del gestor de alias.
     *
     * @var \ForeverPHP\Core\AliasLoader
     */
    private static $instance;

    /**
     * Crea una nueva instancia del gestor de alias.
     *
     * @param array $aliases
     * @return void
     */
    public function __construct($aliases = array()) {
        $this->aliases = $aliases;
    }

    /**
     * Obtiene o crea la instancia singleton del gestor de alias.
     *
     * @param  array $aliases
     * @return \ForeverPHP\Core\AliasLoader
     */
    public static function getInstance($aliases) {
        if (is_null(static::$instance)) {
            static::$instance = new static($aliases);
        }

        return static::$instance;
    }

    /**
     * Carga un alias de clase si está registrado
     *
     * @param  string $alias
     * @return void
     */
    public function load($alias) {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
    }

    /**
     * Antepone el método de carga de la pila de AutoLoader.
     *
     * @return void
     */
    private function prependToLoaderStack() {
        spl_autoload_register(array($this, 'load'), true, true);
    }

    /**
     * Registrar el cargador en la pila de AutoLoader
     *
     * @return void
     */
    public function register() {
        if (!$this->registered) {
            $this->prependToLoaderStack();

            $this->registered = true;
        }
    }
}
