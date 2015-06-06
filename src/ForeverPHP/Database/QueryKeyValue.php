<?php namespace ForeverPHP\Database;

use ForeverPHP\Core\Settings;
use ForeverPHP\Core\Setup;

/**
 * Permite la ejecucion de consultas a motores NoSQL de tipo
 * clave/valor.
 *
 * @since       Version 0.4.0
 */
class QueryKeyValue {
    private $dbSetting = 'default';

    private $database = false;

    private $dbInstance = null;

    private $hasError = false;

    private $error = '';

    /**
     * Contiene la instancia singleton de QueryKeyValue.
     *
     * @var \ForeverPHP\Database\QueryKeyValue
     */
    private static $instance;

    public function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de QueryKeyValue.
     *
     * @return \ForeverPHP\Database\QueryKeyValue
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function using($dbSetting) {
        $this->dbSetting = $dbSetting;
        $this->database = false;
    }

    public function exists($key) {

    }

    public function get($key) {

    }

    public function set($key, $value) {

    }

    public function remove($key) {

    }

    public function hasError() {
        return $this->hasError;
    }

    public function getError() {
        return $this->error;
    }
}
