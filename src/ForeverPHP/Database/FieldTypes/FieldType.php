<?php

namespace ForeverPHP\Database\FieldTypes;

/**
 * FieldTypes:
 *
 * Este archivo contiene todos los tipos de campos para el ORM.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */

class FieldType
{
    protected function loadAttributes($attributes)
    {
        // se deben mesclar los atributos y atributos por defecto
        print_r($default_attributes);
        print_r($attributes);
    }
}
