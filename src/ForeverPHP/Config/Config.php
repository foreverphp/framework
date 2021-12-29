<?php namespace ForeverPHP\Core;

/**
 * Permite administrar la configuracion del sistema y de las aplicaciones.
 *
 * @since       Version 1.0.0
 */
class Config
{
    /**
     * Almacena todos los elementos de configuracion.
     *
     * @var array
     */
    private $configs;

    /**
     * Contiene la instancia singleton de config.
     *
     * @var \ForeverPHP\Core\Config
     */
    private static $instance;

    private function __construct()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de Config.
     *
     * @return \ForeverPHP\Core\Config
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
            static::$instance->load();
        }

        return static::$instance;
    }

    /*
     * Carga una vez el archivo de configuracion
     */
    private function load()
    {
        $path = APPS_ROOT . DS . 'settings.php';

        if (!file_exists($path)) {
            exit('El archivo de configuración settings.php no existe.');
        }

        $this->configs = require $path;

        if (!is_array($this->configs)) {
            exit('El archivo de configuración no tiene el formato correcto.');
        }
    }

    /**
     * Valida si existe el item de configuracion
     *
     * @param  string $name Nombre del item.
     * @return boolean
     */
    public function exists($name)
    {
        if (array_key_exists($name, $this->configs)) {
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
    public function set($name, $value = null)
    {
        if ($value == null) {
            return false;
        }

        $this->configs[$name] = $value;
    }

    /**
     * Obtiene un item de configuracion
     *
     * @param  string $item Nombre del item a obtener.
     * @return mixed        Retorna el valor del item.
     */
    public function get($name)
    {
        if ($this->exists($name)) {
            $value = $this->configs[$name];

            /**
             * NO VA: Si el valor a devolver es una matriz se debe retornar una
             * instancia de SubSettings para recorrer dicha matriz y asi
             * sucesivamente hasta recorrer toda la configuracion.
             */

            /**
             * Idea si se quiere llamar a una configuracion que esta en una matriz
             * dentro del settings.php
             *
             * podria haber dos opciones:
             *     1.- con secciones:
             *         Settings::get($name, $section);
             *
             *     2.- con separaciones por punto:
             *         Settings::get('db.default.port');
             */

            return $value;
        }

        return false;
    }

    /**
     * Abreviación para ver si se está en Debug true.
     *
     * @return boolean
     */
    public function inDebug()
    {
        return $this->get('debug');
    }
}
