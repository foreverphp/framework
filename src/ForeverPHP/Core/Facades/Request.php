<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static void register(?array $params = null)
 * @method static void path()
 * @method static void url()
 * @method static void segment(int $number)
 * @method static void is(string $path)
 * @method static void header(string $name)
 * @method static void server(string $var)
 * @method static \ForeverPHP\Http\Host host()
 * @method static string method()
 * @method static bool isMethod(string $method)
 * @method static void secure()
 * @method static void ajax()
 * @method static void isJson()
 * @method static void wantsJson()
 * @method static void format(string $format)
 * @method static bool exists(string $name)
 * @method static mixed get(string $name, mixed $default = null)
 * @method static mixed all()
 * @method static bool hasFile(string $name)
 * @method static mixed file(string $name)
 * @method static array allFiles()
 * @see \ForeverPHP\Http\Request
 */
class Request extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Http\Request::getInstance();
    }
}
