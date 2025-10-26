<?php

namespace ForeverPHP\Core\CLI;

class Color
{
    private static $foreground = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'red' => '0;31',
        'bold_red' => '1;31',
        'green' => '0;32',
        'bold_green' => '1;32',
        'brown' => '0;33',
        'yellow' => '1;33',
        'blue' => '0;34',
        'bold_blue' => '1;34',
        'purple' => '0;35',
        'bold_purple' => '1;35',
        'cyan' => '0;36',
        'bold_cyan' => '1;36',
        'white' => '1;37',
        'bold_gray' => '0;37',
    ];

    private static $background = [
        'black' => '40',
        'red' => '41',
        'magenta' => '45',
        'yellow' => '43',
        'green' => '42',
        'blue' => '44',
        'cyan' => '46',
        'light_gray' => '47',
    ];

    public static function fg(string $color, string $text): string
    {
        return isset(self::$foreground[$color])
            ? "\033[" . self::$foreground[$color] . "m" . $text . "\033[0m"
            : $text;
    }

    public static function bg(string $color, string $text): string
    {
        return isset(self::$background[$color])
            ? "\033[" . self::$background[$color] . "m" . $text . "\033[0m"
            : $text;
    }
}
