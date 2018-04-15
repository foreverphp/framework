<?php namespace ForeverPHP\Core;

/**
 * Permite controlar todo el flujo de salida canalizandolo por una tuberia,
 * esto permite un mejor control de lo que se le entrega como respuesta al
 * cliente con una salida mas limpia.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 1.0.0
 */
class Stream {
    /**
     * [$data description]
     *
     * @var [type]
     */
    private $data;

    /**
     * [$headerData description]
     *
     * @var [type]
     */
    private $headerData;

    /**
     * [$buffer description]
     *
     * @var [type]
     */
    private $buffer;

    /**
     * [$lastIndex description]
     *
     * @var [type]
     */
    private $lastIndex;

    /**
     * Contiene la instancia singleton de Cache.
     *
     * @var \ForeverPHP\Core\Stream
     */
    private static $instance;

    private function Stream() {
        $this->data = array();
        $this->headerData = array();
        $this->buffer = '';
        $this->lastIndex = 0;
    }

    /**
     * Obtiene o crea la instancia singleton de Stream.
     *
     * @return \ForeverPHP\Core\Stream
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * [__call description]
     *
     * @param  [type] $name      [description]
     * @param  [type] $arguments [description]
     * @return [type]            [description]
     */
    public function __call($name, $arguments)
    {
        // Nota: el valor $name es sensible a mayúsculas.
        echo "Llamando al método de objeto '$name' "
             . implode(', ', $arguments). "\n";
    }

    /**
     * Agrega datos a la tuberia.
     *
     * @param  mixed   $data
     * @param  integer $index
     * @param  boolean $isHeader
     * @return [type]            [description]
     */
    public function pipe($data, $index, $isHeader = false) {
        $this->lastIndex++;
        $this->data[$this->lastIndex] = $data;

    }

    /**
     * Retorna todos los elemento que se encuentran en la tuberia.
     *
     * @return array
     */
    public function getAll() {
        return $this->data;
    }

    /*public function getBuffer() {
        return $this->buffer;
    }*/

    /**
     * [remove description]
     *
     * @param  integer $index
     * @return [type]        [description]
     */
    public function remove($index) {

    }

    /**
     * Limpia el Stream.
     */
    public function clean() {
        $this->data = array();
        $this->buffer = '';
        $this->lastIndex = 0;
    }

    /**
     * Renderiza el contenido de Stream para ser enviado al cliente.
     */
    public function render() {
        echo $this->buffer;
    }
}
