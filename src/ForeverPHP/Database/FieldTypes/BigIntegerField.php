<?php

namespace ForeverPHP\Database\FieldTypes;

use ForeverPHP\Database\FieldTypes\FieldType;

class BigIntegerField extends FieldType
{
    private $defaultAttributes = [
        'autoincrement' => false,
        'null' => true,
        'unique' => false,
        'primary_key' => false,
        'default' => null,
    ];

    public function __construct($attributes)
    {
        $this->loadAttributes($attributes);
    }
}
