<?php
namespace ForeverPHP\View;

/**
 * Permite administrar de forma mas amigable la variables a trabajar
 * en el template.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class Context {
    /**
     * Almacena todos los items del contexto.
     *
     * @var array
     */
    private $items = array();

    public function __construct($items = array()) {
        $this->items = $items;
    }

    public function exists($name) {
        if (array_key_exists($name, $this->items)) {
            return true;
        }

        return false;
    }

    public function set($name, $value) {
        $this->items[$name] = $value;
    }

    public function get($name) {
        $value = null;

        if ($this->exists($name)) {
            $value = $this->items[$name];
        }

        return $value;
    }

    public function all() {
        return $this->items;
    }

    public function remove($name) {
        if ($this->exists($name)) {
            unset($this->items[$name]);
        }
    }

    /**
     * Permite conbinar otro contexto a este.
     *
     * @param  Context $context
     * @return boolean
     */
    public function merge($context) {

    }

    /**
     * Permite conbinar al contexto actual con un contexto de una
     * aplicacion diferente a la actual.
     *
     * @param  string $path_context
     * @return boolean
     */
    public function mergeFromApp($pathContext) {

    }
}
