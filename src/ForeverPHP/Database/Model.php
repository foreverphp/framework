<?php namespace ForeverPHP\Database;

/**
 * ORM: para bases de datos relacionales.
 * ODM: para bases de datos no relacionales basadas en documentos.
 *
 * Model es el motor ORM estandar del framework, por el momento solo trabaja con
 * bases de datos relacionales.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 1.0.0
 */
class Model extends QuerySet
{
    protected $data = array(); // Almacena el contenido que se va a trabajar en el modelo

    public $db_config = 'default'; // Indica que configuracion de base de datos utilizara el modelo

    public function __construct()
    {
        /**
         * El tipo de motor de datos se saca de la configuracion de base de datos,
         * por defecto es 'default' de ahi se obtiene el motor de datos a cargar
         */
        $db = Setting::get('db');
    }

    public function create()
    {
        //
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        //
    }

    public function save()
    {
        //
    }

    public function find($limit = 1)
    {
        // Si no se especifica el limite devolvera solo un registro
    }

    public function all()
    {
        //
    }

    public function delete()
    {
        //
    }

    public function orderBy()
    {
        //
    }

    public function where()
    {
        //
    }
}
