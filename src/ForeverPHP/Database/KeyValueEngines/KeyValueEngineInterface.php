<?php namespace ForeverPHP\Database\KeyValueEngines;

/**
 * Interface que deben implementar si o si todos los motores de datos,
 * tipo clave/valor.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
interface KeyValueEngineInterface
{
    public function __construct($dbSetting);
    /*public function selectDatabase($database);
    public function connect();
    public function query($query);
    public function setParameters($parameters);
    public function execute();
    public function disconnect();*/
    public function __destruct();
}
