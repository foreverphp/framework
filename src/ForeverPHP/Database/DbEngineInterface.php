<?php namespace ForeverPHP\Database;

/**
 * Interface que deben implementar si o si todos los motores de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
interface DbEngineInterface {
	public function __construct($dbSetting);
	public function selectDatabase($database);
    public function connect();
	public function query($query);
	public function setParameters($parameters);
	public function execute();
    public function disconnect();
	public function __destruct();
}
