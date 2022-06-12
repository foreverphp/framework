<?php namespace ForeverPHP\Cache;

/**
 * Interface base para los motores de Cache.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
 */
interface CacheInterface
{
    public function exists($key);
    public function set($key, $value);
    public function get($key);
    public function remove($key);
}
