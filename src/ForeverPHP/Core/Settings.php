<?php namespace ForeverPHP\Core;

/**
 * Permite administrar la configuracion del sistema y de las aplicaciones.
 *
 * @since       Version 0.1.0
 */
class Settings {
    /**
     * Almacena todos los elementos de configuracion.
     *
     * @var array
     */
    private $settings;

    /**
     * Contiene la instancia singleton de Settings.
     *
     * @var \ForeverPHP\Core\Settings
     */
    private static $instance;

    private function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de Settings.
     *
     * @return \ForeverPHP\Core\Settings
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
            static::$instance->load();
        }

        return static::$instance;
    }

    /*
     * Carga una vez el archivo de configuracion
     */
    private function load() {
        $path = APPS_ROOT . DS . 'settings.php';

        if (!file_exists($path)) {
            exit("El archivo de configuración settings.php no existe.");
        }

        $this->settings = require $path;

        if (!is_array($this->settings)) {
            exit('El archivo de configuración no tiene el formato correcto.');
        }
    }

    /**
     * Valida si existe el item de configuracion
     *
     * @param  string $name Nombre del item.
     * @return boolean
     */
    public function exists($name) {
        if (array_key_exists($name, $this->settings)) {
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
    public function set($name, $value = null) {
        if ($value == null) {
            return false;
        }

        $this->settings[$name] = $value;
    }

    /**
     * Obtiene un item de configuracion
     *
     * @param  string $item Nombre del item a obtener.
     * @return mixed        Retorna el valor del item.
     */
    public function get($name) {
        if ($this->exists($name)) {
            return $this->settings[$name];
        }

        return false;
    }

    /**
     * Abreviación para ver si se está en Debug true.
     *
     * @return boolean
     */
    public function inDebug() {
        return $this->get('debug');
    }

    /**
     * Controla las llamadas a métodos no estáticos cuando no se esta
     * utilizando el decorador de Settings.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments) {
        $numArgs = count($arguments);

        if ($name === 'exists') {
            if ($numArgs == 1) {
                return static::getInstance()->exists($arguments[0]);
            }
        } elseif ($name === 'set') {
            if (count($arguments) == 2) {
                return static::getInstance()->set($arguments[0], $arguments[1]);
            }
        } elseif ($name === 'get') {
            if (count($arguments) == 1) {
                return static::getInstance()->get($arguments[0]);
            }
        } else {
            throw new \RuntimeException("El método ($name) no existe.");
        }

        /*
         * Si los argumentos entregados no son los correctos se lanza una
         * excepción
         */
        throw new \RuntimeException("El número de argumentos para ($name), no es valido.");
    }
}
