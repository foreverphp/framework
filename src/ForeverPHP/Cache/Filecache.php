<?php namespace ForeverPHP\Cache;

use ForeverPHP\Cache\CacheInterface;

/**
 * Realiza cache con archivos en disco, es la forma basica del cache.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
 */
class FileCache implements CacheInterface
{
    /**
     * Ruta del directorio a usar para el cache.
     *
     * @var string
     */
    private $location;

    public function __construct($location)
    {
        $this->location = $location;

        // Verifica si existe el directorio del cache, si no se crea
        if (!file_exists($this->location)) {
            mkdir($this->location, 0777);
        }
    }

    /**
     * Valida si existe el archivo en el directorio cache.
     *
     * @param  string $key
     * @return boolean
     */
    public function exists($key)
    {
        $filenameCache = $this->location . DS . $key;

        if (file_exists($filenameCache)) {
            return true;
        }

        return false;
    }

    /**
     * Almacena un nuevo archivo en cache.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        try {
            $filenameCache = $this->location . DS . $key;

            // Escribe el archivo en cache
            file_put_contents($filenameCache, $value);
        } catch (\Exception$e) {
            return false;
        }

        return true;
    }

    public function get($key)
    {

    }

    public function remove($key)
    {

    }
}
