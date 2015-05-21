<?php namespace ForeverPHP\Cache;

/**
 * Interface base para los motores de Cache.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
 */
interface CacheInterface {
    public function exists($name);
    public function set($name, $value);
    public function get($name);
    public function remove($name);
}
