<?php namespace ForeverPHP\Filesystem;

/**
 * Permite administrar el sistema de archivos.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.3.0
 */
class FilesystemException extends \Exception {}

/*
 * Se debe poder trabajar con diferentes sistemas de archivos.
 * Como: local, google drive, amazon s3, dropbox
 */

class Filesystem {
    /**
     * Determina si un archivo existe.
     *
     * @param  string $path
     * @return bool
     */
    public function exists($path) {
        return file_exists($path);
    }

    public function get() {

    }

    public function put() {

    }

    /**
     * Elimina un archivo de la ruta determinada.
     *
     * @param  string|array $paths
     * @return bool
     */
    public function delete($paths) {
        $paths = is_array($paths) ? $paths : func_get_args();

        $return = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $return = false;
                }
            } catch (ErrorException $e) {
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Mueve un archivo a una nueva ubicación.
     *
     * @param  string $path
     * @param  string $target
     * @return bool
     */
    public function move($path, $target) {
        return rename($path, $target);
    }

    /**
     * Copia un archivo a una nueva ubicación.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public function copy($path, $target)
    {
        return copy($path, $target);
    }

    public function prueba() {
        echo "prueba fachada.".'<br>';
    }

    public function prueba1($p1) {
        echo "prueba fachada.".$p1.'<br>';
    }

    public function prueba2($p1, $p2) {
        echo "prueba fachada.".$p1.$p2.'<br>';
    }

    public function prueba3($p1, $p2, $p3) {
        echo "prueba fachada.".$p1.$p2.$p3.'<br>';
    }

    public function prueba4($p1, $p2, $p3, $p4) {
        echo "prueba fachada.".$p1.$p2.$p3.$p4.'<br>';
    }

    public function pruebaMas($p1, $p2, $p3, $p4, $p5) {
        echo "prueba fachada.".$p1.$p2.$p3.$p4.$p5.'<br>';
    }

    public function pruebaMas2($p1, $p2, $p3, $p4, $p5, $p6, $p7) {
        echo "prueba fachada.".$p1.$p2.$p3.$p4.$p5.$p6.$p7.'<br>';
    }

    /**
     * Crea un nuevo directorio.
     *
     * @param  string  $path
     * @param  string  $permissions
     * @param  boolean $recursive
     * @return boolean
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false) {
        if (!file_exists($path)) {
            return mkdir($path, $mode, $recursive);
        }

        return true;
    }
}
