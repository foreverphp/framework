<?php

namespace ForeverPHP\Database\FieldTypes;

use ForeverPHP\Database\FieldTypes\FieldType;

class DecimalField
{
    private $length = 10;
    private $decimals = 0;
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $default = null;

    public function __construct($attributes)
    {
    }
}
