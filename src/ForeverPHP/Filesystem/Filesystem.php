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
     * @return boolean
     */
    public function exists($path) {
        return file_exists($path);
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
