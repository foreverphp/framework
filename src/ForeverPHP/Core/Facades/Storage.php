<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $path)
 * @method static string get(string $path)
 * @method static string put(string $path, string $contents, bool $lock = false)
 * @method static bool delete(mixed $paths)
 * @method static bool move(string $path, string $target)
 * @method static bool copy(string $path, string $target)
 * @method static array|string name(string $path)
 * @method static array|string extension(string $path)
 * @method static bool|string type(string $path)
 * @method static bool|string mimeType(string $path)
 * @method static bool|int size(string $path)
 * @method static bool isDirectory(string $path)
 * @method static bool isWritable(string $path)
 * @method static bool isFile(string $path)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false)
 * @see \ForeverPHP\Filesystem\Filesystem
 */
class Storage extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return new \ForeverPHP\Filesystem\Filesystem();
    }
}
