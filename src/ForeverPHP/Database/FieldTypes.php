<?php namespace ForeverPHP\Database;

/**
 * FieldTypes:
 *
 * Este archivo contiene todos los tipos de campos para el ORM.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class FieldType {
    protected function _load_attributes($attributes) {
        // se deben mesclar los atributos y atributos por defecto
        print_r($default_attributes);
        print_r($attributes);
    }
}

class BigIntegerField extends FieldType {
    private $_default_attributes = array(
        'autoincrement' => false,
        'null' => true,
        'unique' => false,
        'primary_key' => false,
        'default' => null
    );

    public function __construct($attributes) {
        $this->_load_attributes($attributes);
    }
}

class BinaryField {
    private $snull = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public  function __construct($attributes) {

    }
}

class BooleanField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class CharField {
    private $length = 10;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class DateField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class DateTimeField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class DecimalField {
    private $length = 10;
    private $decimals = 0;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class EmailField {
    private $length = 80;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class FloatField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class ImageField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $upload_to = '';
    public $default = null;

    public function __construct($attributes) {

    }
}

class IntegerField extends FieldType {
    private $attributes = array(

    );
    private $autoincrement = false;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class TextField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class TimeField {
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class URLField {
    private $length = 80;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}

class VarcharField {
    private $length = 50;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes) {

    }
}
