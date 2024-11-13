<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static void pipe(string $name, array $arguments)
 * @method static array getAll()
 * @method static void remove(int $index)
 * @method static void clean()
 * @method static void render()
 * @see \ForeverPHP\Core\Stream
 */
class Stream extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Core\Stream::getInstance();
    }
}
