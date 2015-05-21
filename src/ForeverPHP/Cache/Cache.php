<?php namespace ForeverPHP\Cache;

use ForeverPHP\Core\Facades\Settings;

/**
 * Permite el trabajo con Cache con diferentes motores.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
 */

/*
 given a URL, try finding that page in the cache
if the page is in the cache:
    return the cached page
else:
    generate the page
    save the generated page in the cache (for next time)
    return the generated page
 */

/*
OJO: no va por url

¿Como se almacenan los templates?

    inicio.html.cache -> fonde inicio es el nombre del template

    Uso:
        Cache::set($tamplate . '.html', $render);

¿Como se almacenan los resultados de las consultas?

    mi_resultado_consulta.cache -> mi_resultado_consulta lo define el usuario
    o el ORM (tentacles)

    Uso:
        Cache::set('mi_resultado_consulta', $data);

        Nota: Los resultados se deberian guardar en formato JSon para una mejor
              comprension.

Cache::get: antes de devolver el objeto del cache debe verificar si ha expirado.

Cache::set: antes de guardar el objeto en el cache debe verificar que no se
            exceda el limite maximo de entidades.
*/

class Cache {
    /**
     * Indica si el cache esta activo.
     *
     * @var boolean
     */
    private $cacheEnabled;

    /**
     * El tipo de motor a utilizar.
     *
     * @var string
     */
    private $engine;

    /**
     * Ubicación del cache, solo algunos motores usan esta opción.
     *
     * @var string
     */
    private $location;

    /**
     * Tiempo de refresco del cache, esta representado en segundos.
     *
     * @var int
     */
    private $timeout;

    /**
     * Numero máximo de entradas en cache.
     *
     * @var int
     */
    private $maxEntries;

    /**
     * Contiene la instancia del motor de cache.
     *
     * @var mixed
     */
    private $cacheEngine;

    /**
     * Contiene la instancia singleton de Cache.
     *
     * @var \ForeverPHP\Cache\Cache
     */
    private static $instance;

    private function __construct() {
        $this->cacheEnabled = false;
    }

    /**
     * Obtiene o crea la instancia singleton de Cache.
     *
     * @return \ForeverPHP\Cache\Cache
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
            static::$instance->load();
        }

        return static::$instance;
    }

    /**
     * Carga una vez el motor de cache y la
     * configuración del cache.
     *
     * @return void
     */
    private function load() {
        // Verifico si el cache esta activo
        if (Settings::get('cacheEnabled')) {
            $this->cacheEnabled = true;
        }

        // Sigue cargando el motor de cache, solo su esta activo
        if ($this->cacheEnabled) {
            $cacheSettings = Settings::get('cache');

            // Se almacenan los ítems de configuración del cache
            $this->engine = $cacheSettings['engine'];
            $this->location = $cacheSettings['location'];
            $this->timeout = $cacheSettings['timeout'];
            $this->maxEntries = $cacheSettings['maxEntries'];

            // Se crea la instancia del motor según la configuración
            if ($this->engine === 'filecache') {
                $this->cacheEngine = new \ForeverPHP\Cache\FileCache($this->location);
            }
        }
    }

    /**
     * Valida si la entrada existe en el cache.
     *
     * @param  string $key
     * @return boolean
     */
    public function exists($key) {
        if ($this->cacheEnabled) {
            return $this->cacheEngine->exists($key);
        }

        return false;
    }

    /**
     * Almacena una entrada en el cache.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value) {
        if ($this->cacheEnabled) {
            $this->cacheEngine->set($key, $value);
        }
    }

    public function get($key) {
        // NO DISPONIBLE POR AHORA, YA QUE SE ESTAN HACIENDO PRUEBAS CON EL CACHE
    }

    public function remove($key) {
        // NO DISPONIBLE POR AHORA, YA QUE SE ESTAN HACIENDO PRUEBAS CON EL CACHE
    }
}
