<?php

namespace ForeverPHP\Database\Enums;

enum ParameterType: string
{
    case INT = 'i';
    case DOUBLE = 'd';
    case STRING = 's';
    case BOOL = 'b';
}
