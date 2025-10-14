<?php

namespace ForeverPHP\Filesystem;

use ForeverPHP\Core\Facades\Redirect;
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

    private function sanitizeFilename(string $filename): string
    {
        // Remover caracteres peligrosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Evitar nombres vacíos
        return $filename ?: 'download';
    }

    private function readfileChunked(string $filepath, int $chunkSize = 8192): void
    {
        $handle = fopen($filepath, 'rb');

        if ($handle === false) {
            return;
        }

        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            flush(); // Forzar envío al navegador
        }

        fclose($handle);
    }

    /**
     * Envía un archivo al cliente para su descarga de forma segura.
     *
     * Este método valida la existencia, permisos y ubicación del archivo dentro
     * del directorio permitido antes de enviarlo. Permite además asignar un nombre
     * alternativo para el archivo descargado sin alterar el archivo físico.
     *
     * El envío se realiza en chunks para optimizar el uso de memoria y soportar
     * archivos grandes. Se envían cabeceras HTTP seguras y se limpian los buffers
     * de salida previos.
     *
     * @param string      $filePath     Ruta completa del archivo a descargar.
     * @param string|null $newFilename  (Opcional) Nombre con el que se ofrecerá
     *                                  el archivo al usuario durante la descarga.
     *                                  Si se omite, se usa el nombre real del archivo.
     *
     * @return bool  Devuelve `true` si la descarga comienza correctamente,
     *               o `false` si ocurre un error (por ejemplo, archivo no encontrado
     *               o sin permisos).
     *
     * @throws void  No lanza excepciones, pero puede finalizar la ejecución con `exit`
     *               después de enviar los encabezados y el contenido del archivo.
     *
     * @uses sanitizeFilename()  Para limpiar el nombre del archivo de salida.
     * @uses readfileChunked()   Para enviar el archivo en bloques (chunks).
     *
     * @note Esta función envía cabeceras HTTP y termina la ejecución del script.
     *       No debe llamarse después de haber enviado salida al navegador.
     *       Se recomienda que cualquier manejo de errores previos se realice antes.
     *
     * @example
     * ```php
     * // Descarga normal
     * $this->download('/var/www/storage/reports/invoice.pdf');
     *
     * // Descarga con nombre alternativo
     * $this->download('/var/www/storage/reports/invoice.pdf', 'invoice_2025.pdf');
     * ```
     */
    public function download(string $filePath, ?string $newFilename = null): bool
    {
        // Validar que el archivo existe
        if (!$this->exists($filePath)) {
            Redirect::error(404);
            return false;
        }

        // Obtener ruta absoluta y validar que esté en directorio permitido
        $realPath = realpath($filePath);
        $allowedDir = realpath(ROOT_PATH);

        if ($realPath === false || strpos($realPath, $allowedDir) !== 0) {
            Redirect::error(403);
            return false;
        }

        // Verificar permisos de lectura
        if (!is_readable($realPath)) {
            Redirect::error(403);
            return false;
        }

        // Obtener información del archivo
        $filesize = filesize($realPath);
        $filenameSafe = $this->sanitizeFilename(basename($newFilename ?? $realPath));

        // Limpiar buffers de salida
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Enviar headers seguros
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filenameSafe\"");
        header(
            "Content-Disposition: attachment; filename=\"$filenameSafe\"; filename*=UTF-8''" .
            rawurlencode($filenameSafe)
        );
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: $filesize");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');

        // Enviar archivo en chunks para archivos grandes
        $this->readfileChunked($realPath);

        exit;
    }
}
