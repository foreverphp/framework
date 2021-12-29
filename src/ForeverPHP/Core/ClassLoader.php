<?php namespace ForeverPHP\Core;

class ClassLoader
{
    /**
     * Los directorios registrados.
     *
     * @var array
     */
    private static $directories = array();

    /**
     * Indica si ClassLoader esta correctamente registrado.
     *
     * @var boolean
     */
    private static $registered = false;

    /**
     * Carga el archivo de la clase dada.
     *
     * @param  string $class
     * @return boolean
     */
    public static function load($class)
    {
        $class = static::normalizeClass($class);

        foreach (static::$directories as $directory) {
            if (file_exists($path = $directory . DS . $class)) {
                require_once $path;

                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene el nombre de la clase normalizado.
     *
     * @param  string $class
     * @return string
     */
    public static function normalizeClass($class)
    {
        if ($class[0] == '\\') {
            $class = substr($class, 1);
        }

        return str_replace(array('\\', '_'), DS, $class) . '.php';
    }

    /**
     * Registra el cargador de clases en la pila de auto-loader.
     *
     * @return void
     */
    public static function register()
    {
        if (!static::$registered) {
            static::$registered = spl_autoload_register(array('ForeverPHP\Core\ClassLoader', 'load'));
        }
    }

    /**
     * Agrega directorios al cargador de clases.
     *
     * @param  string|array $directories
     * @return void
     */
    public static function addDirectories($directories)
    {
        static::$directories = array_unique(array_merge(static::$directories, (array) $directories));
    }

    /**
     * Remueve directorios del cargador de clases.
     *
     * @param  string|array $directories
     * @return void
     */
    public static function removeDirectories($directories = null)
    {
        if (is_null($directories)) {
            static::$directories = array();
        } else {
            static::$directories = array_diff(static::$directories, (array) $directories);
        }
    }

    /**
     * Obtiene todos los directorios registrados en el cargador
     *
     * @return array
     */
    public static function getDirectories()
    {
        return static::$directories;
    }
}
