<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $path)
 * @method static string|false get(string $path)
 * @method static int|false put(string $path, mixed $contents, bool $lock = false)
 * @method static bool delete(string|array $paths)
 * @method static bool move(string $path, string $target)
 * @method static bool copy(string $path, string $target)
 * @method static array|string name(string $path)
 * @method static array|string extension(string $path)
 * @method static string|bool type(string $path)
 * @method static string|bool mimeType(string $path)
 * @method static int|bool size(string $path)
 * @method static bool isDirectory(string $path)
 * @method static bool isWritable(string $path)
 * @method static bool isFile(string $path)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false)
 * @method static bool download(string $filePath, ?string $newFilename = null)
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
