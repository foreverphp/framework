<?php namespace ForeverPHP\View;

/**
 * Permite administrar de forma mas amigable la variables a trabajar
 * en el template.
 *
 * @since       Version 0.2.0
 */
class Context
{
    /**
     * Almacena los contextos.
     *
     * @var array
     */
    private $contexts;

    /**
     * Almacena los contextos globales.
     *
     * @var array
     */
    private $globalContexts;

    /**
     * Indica si usaran o no contextos globales, al retornar
     * todos los contextos.
     *
     * @var bool
     */
    private $useGlobalContexts;

    /**
     * Contiene la instancia singleton de Context.
     *
     * @var \ForeverPHP\View\Context
     */
    private static $instance;

    private function __construct()
    {
        $this->contexts = array();
        $this->globalContexts = array();
        $this->useGlobalContexts = true;
    }

    /**
     * Obtiene o crea la instancia singleton de Context.
     *
     * @return \ForeverPHP\View\Context
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function exists($name)
    {
        if (array_key_exists($name, $this->contexts)) {
            return true;
        } else {
            if (array_key_exists($name, $this->globalContexts)) {
                return true;
            }
        }

        return false;
    }

    private function set($name, $value, $global = false)
    {
        if ($global) {
            $this->globalContexts[$name] = $value;
        } else {
            $this->contexts[$name] = $value;
        }
    }

    private function setArray($values, $global)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $global);
        }
    }

    /**
     * Controlador de llamadas dinamicas, hacia el objeto.
     *
     * @param  string $method
     * @param  array $args
     * @return void
     */
    public function __call($method, $args)
    {
        switch (count($args)) {
            case 1:
                if ($method === 'set' && is_array($args[0])) {
                    return $this->setArray($args[0], false);
                }

                return $this->$method($args[0]);
            case 2:
                if ($method == 'set' && is_array($args[0])) {
                    return $this->setArray($args[0], $args[1]);
                }

                return $this->$method($args[0], $args[1]);
            case 3:
                return $this->$method($args[0], $args[1], $args[2]);
        }
    }

    public function get($name, $global = false)
    {
        $value = null;

        if ($this->exists($name)) {
            if ($global) {
                $value = $this->globalContexts[$name];
            } else {
                $value = $this->contexts[$name];
            }
        }

        return $value;
    }

    public function useGlobal($value)
    {
        $this->useGlobalContexts = $value;
    }

    public function all()
    {
        $contentContext = $this->contexts;

        if ($this->useGlobalContexts) {
            $contentContext = array_merge($contentContext, $this->globalContexts);
        }

        return $contentContext;
    }

    public function remove($name, $global = false)
    {
        if ($this->exists($name)) {
            unset($this->contexts[$name]);
        }
    }

    public function removeAll()
    {
        $this->contexts = array();
    }
}
