<?php namespace ForeverPHP\Database\KeyValueEngines;

/**
 * Clase base para los motores de base de datos, tipo
 * clave/valor.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.4.0
 */
class KeyValueEngine
{
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

    public function __construct($dbSetting)
    {
        $this->dbSetting = $dbSetting;
        $this->numRows = 0;
        $this->parameters = array();
    }

    public static function getInstance($dbSetting = 'default')
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($dbSetting);
        }

        return static::$instance;
    }

    public function selectDatabase($database)
    {
        $this->database = $database;
    }

    public function query($query, $type = 'other', $return = 'num')
    {
        $this->query = $query;
        $this->queryType = $type;
        $this->queryReturn = $return;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getNumRows()
    {
        return $this->numRows;
    }

    public function get()
    {
        echo $this->dbSetting;
    }
}
