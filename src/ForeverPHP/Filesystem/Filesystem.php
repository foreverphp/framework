<?php

namespace ForeverPHP\Filesystem;

use ForeverPHP\Filesystem\FileNotFoundException;

/**
 * Permite administrar el sistema de archivos.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.3.0
 */
class Filesystem
{
    /**
     * Determina si un archivo existe.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Obtiene el contenido del archivo.
     *
     * @param string $path
     * @return string|false
     * @throws \ForeverPHP\Filesystem\FileNotFoundException
     */
    public function get(string $path): string|false
    {
        if ($this->isFile($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exist at path {$path}");
    }

    /**
     * Escribir el contenido a un archivo.
     *
     * @param string $path
     * @param mixed $contents
     * @param bool $lock
     * @return int|false
     */
    public function put(string $path, mixed $contents, bool $lock = false): int|false
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Elimina un archivo de la ruta determinada.
     *
     * @param string|array $paths
     * @return bool
     */
    public function delete(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $return = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $return = false;
                }
            } catch (\ErrorException $e) {
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Mueve un archivo a una nueva ubicación.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function move(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Copia un archivo a una nueva ubicación.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Extrae el nombre del archivo de una ruta de archivo.
     *
     * @param string $path
     * @return array|string
     */
    public function name(string $path): array|string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extrae la extensión del archivo de una ruta de archivo.
     *
     * @param string $path
     * @return array|string
     */
    public function extension(string $path): array|string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Obtiene el tipo de archivo de un archivo determinado.
     *
     * @param string $path
     * @return string|bool
     */
    public function type(string $path): string|bool
    {
        return filetype($path);
    }

    /**
     * Obtiene el tipo MIME de un archivo determinado.
     *
     * @param string $path
     * @return string|bool
     */
    public function mimeType(string $path): string|bool
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Obtiene el tamaño del archivo dado.
     *
     * @param string $path
     * @return int|bool
     */
    public function size(string $path): int|bool
    {
        return filesize($path);
    }

    /**
     * Determina si la ruta dada es un directorio.
     *
     * @param string $directory
     * @return bool
     */
    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }
    /**
     * Determina si la ruta dada se puede escribir.
     *
     * @param string $path
     * @return bool
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Determina si la ruta dada es un archivo.
     *
     * @param string $file
     * @return bool
     */
    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * Crea un nuevo directorio.
     *
     * @param string $path
     * @param int $permissions
     * @param bool $recursive
     * @return bool
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        if (!file_exists($path)) {
            return mkdir($path, $mode, $recursive);
        }

        return true;
    }
}
