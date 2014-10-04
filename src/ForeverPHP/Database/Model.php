<?php
/**
 * foreverPHP - Framework MVT (Model - View - Template)
 *
 * Model:
 *
 * Model es el motor ORM estandar del framework, por el momento solo trabaja con
 * bases de datos relacionales.
 *
 * @package     foreverPHP
 * @subpackage  model
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @copyright   Copyright (c) 2014, Emarva.
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @link        http://www.emarva.com/foreverphp
 * @since       Version 0.2.0
 */

class Model extends QuerySet {
    protected $data = array();       // Almacena el contenido que se va a trabajar en el modelo

	public $db_config = 'default';	// Indica que configuracion de base de datos utilizara el modelo

    public function __construct() {
    	/*
    	 * El tipo de motor de datos se saca de la configuracion de base de datos,
    	 * por defecto es 'default' de ahi se obtiene el motor de datos a cargar
    	 */
    	$db = Setting::get('db');
    }

    public function create() {

    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function __get($name) {

    }

    public function save() {

    }

    public function find($limit = 1) {
    	// Si no se especifica el limite devolvera solo un registro

    }

    public function all() {

    }

    public function delete() {

    }



    public function order_by() {

    }

    public function where() {

    }
}
