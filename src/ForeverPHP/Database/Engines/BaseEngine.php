<?php namespace ForeverPHP\Database\Engines;

/**
 * Clase base para los motores de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.3.0
 */
class BaseEngine  {
    protected $dbSetting;
    protected $database;
    protected $link;
    protected $query;
    protected $queryType = 'other';
    protected $queryReturn = 'num';
    protected $parameters;
    protected $error;
    protected $numRows;

    protected static $instance;

    public function __construct($dbSetting, $database) {
        $this->dbSetting = $dbSetting;
        $this->database = $database;
        $this->numRows = 0;
        $this->parameters = array();
    }

    public static function getInstance($dbSetting = 'default', $database = false) {
        if (is_null(static::$instance)) {
            static::$instance = new static($dbSetting, $database);
        }

        return static::$instance;
    }

    public function query($query, $type = 'other', $return = 'num') {
        $this->query = $query;
        $this->queryType = $type;
        $this->queryReturn = $return;
    }

    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function getError() {
        return $this->error;
    }

    public function getNumRows() {
        return $this->numRows;
    }

    public function get() {
        echo $this->dbSetting;
    }
}
