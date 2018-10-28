<?php namespace ForeverPHP\Http;

use ForeverPHP\Core\Settings;

/**
 * Contiene información de un archivo enviado por el request.
 *
 * @since       Version 0.4.0
 */
class RequestFile {

    private $filename = '';

    private $name = '';

    private $extension = '';

    private $mimetype = '';

    private $realPath = '';

    private $error = null;

    private $size = 0;

    public function __construct($fileInfo) {
        $this->filename = $fileInfo['name'];

        // Valida si el nombre del archivo viene en array
        if (is_array($this->filename)) {
            $this->filename = $this->filename[0];
        }

        // Obtengo el nombre y la extención del archivo
        $nameAndExtension = explode('.', $this->filename);
        $this->name = $nameAndExtension[0];
        $this->extension = $nameAndExtension[1];

        $this->mimetype = $fileInfo['type'];
        $this->realPath = $fileInfo['tmp_name'];
        $this->error = $fileInfo['error'];
        $this->size = $fileInfo['size'];
    }

    public function getFilename() {
        return $this->filename;
    }

    public function getName() {
        return $this->name;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function getMimeType() {
        return $this->mimetype;
    }

    public function getRealPath() {
        return $this->realPath;
    }

    public function hasError() {
        if ($this->error != 0) {
            return true;
        }

        return false;
    }

    public function getSize() {
        return $this->size;
    }

    /**
     * Mueve el archivo a la ruta entregada.
     *
     * @param  string $path
     * @return boolean
     */
    public function move($path, $filename = null) {
        $newFilename = ($filename != null) ? $filename : $this->filename;

        // Valida si la ruta termina con slash
        $lastSlash = substr($path, strlen($path), 1);

        if ($lastSlash !== '/') {
            $path .=  '/';
        }

        // Retorno TRUE si se movio el archivo, de lo contrario FALSE
        $result = move_uploaded_file($this->realPath, $path . $newFilename);

        return $result;
    }
}
