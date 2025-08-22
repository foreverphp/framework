<?php

namespace ForeverPHP\Database\FieldTypes;

use ForeverPHP\Database\FieldTypes\FieldType;

class EmailField
{
    private $length = 80;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes)
    {
    }
}
