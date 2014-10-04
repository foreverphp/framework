<?php
namespace ForeverPHP\Database\Engines;

/**
 * Clase base para los motores de base de datos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.3.0
 */
class BaseEngine  {
    protected $dbSetting;
    protected $link;
    protected $query;
    protected $queryType = 'other';
    protected $queryReturn = 'num';
    protected $parameters;
    protected $error;
    protected $numRows;

    protected static $instance;

    public function __construct($dbSetting) {
        $this->dbSetting = $dbSetting;
        $this->numRows = 0;
        $this->parameters = array();
    }

    public static function getInstance($dbSetting = 'default') {
        if (is_null(static::$instance)) {
            static::$instance = new static($dbSetting);
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

/* If we have to retrieve large amount of data we use MYSQLI_USE_RESULT */
//if ($result = mysqli_query($link, "SELECT * FROM City", MYSQLI_USE_RESULT)) {

    /* Note, that we can't execute any functions which interact with the
       server until result set was closed. All calls will return an
       'out of sync' error */
  /*  if (!mysqli_query($link, "SET @a:='this will not work'")) {
        printf("Error: %s\n", mysqli_error($link));
    }
    mysqli_free_result($result);
}*/
}
