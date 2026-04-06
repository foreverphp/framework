<?php

namespace ForeverPHP\Database\Enums;

enum FetchMode: string
{
    case ASSOC = 'assoc';
    case NUM = 'num';
    case BOTH = 'both';
    case OBJECT = 'object';
}
