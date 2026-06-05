<?php

namespace ForeverPHP\Core;

/**
 * Permite administrar la configuracion del sistema y de las aplicaciones.
 *
 * @since       Version 0.1.0
 */
class Settings
{
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
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
            static::$instance->load();
        }

        return static::$instance;
    }

    /**
     * Carga una vez el archivo de configuracion
     */
    private function load()
    {
        $path = APPS_ROOT . DS . 'settings.php';

        if (!file_exists($path)) {
            exit('El archivo de configuración settings.php no existe.');
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
    public function exists(string $name): bool
    {
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
    public function set(string $name, mixed $value = null): bool
    {
        if ($value == null) {
            return false;
        }

        $this->settings[$name] = $value;
        return true;
    }

    /**
     * Obtiene un item de configuracion
     *
     * @param  string $item Nombre del item a obtener.
     * @return mixed        Retorna el valor del item.
     */
    public function get(string $name): mixed
    {
        if ($this->exists($name)) {
            $value = $this->settings[$name];

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
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key);
        return is_string($value) ? $value : $default;
    }

    /**
     * @param string $key
     * @param int $default
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param string $key
     * @param float $default
     * @return float
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key);
        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        return is_string($value) || is_numeric($value) ? (bool) $value : $default;
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $default
     * @return array<array-key, mixed>
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key);
        return is_array($value) ? $value : $default;
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
