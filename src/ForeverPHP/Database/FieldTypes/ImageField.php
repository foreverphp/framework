<?php

namespace ForeverPHP\Database\FieldTypes;

use ForeverPHP\Database\FieldTypes\FieldType;

class ImageField
{
    private $null = true;
    private $unique = false;
    private $primary_key = false;
    private $upload_to = '';
    public $default = null;

    public function __construct($attributes)
    {
    }
}
