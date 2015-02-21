<?php namespace ForeverPHP\Security;

use ForeverPHP\Core\Exceptions\SecurityException;

/**
 * Permite generar diversos tipos de hash.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class Hash {
    /**
     * Contiene la instancia singleton de Hash.
     *
     * @var \ForeverPHP\Security\Hash
     */
    private static $instance;

    public function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de App.
     *
     * @return \ForeverPHP\Security\Hash
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function makeMD5($value) {
        return md5($value);
    }

    private function makeSHA1($value) {

    }

    public function make($values, $type = 'md5') {
        $valueToHash = '';    // Alamacena el valor a hashear, puede ser una matriz
        $newHash = '';         // Alamacena el valor ya hasheado

        if (is_array($values)) {
            foreach ($values as $value) {
                $valueToHash .= $value;
            }
        } else {
            $valueToHash = $values;
        }

        if ($type === 'md5') {
            $newHash = $this->makeMD5($valueToHash);
        } elseif ($type === 'sha1') {
            $newHash = $this->makeSHA1($valueToHash);
        } else {
            // Formato no compatible
            throw new SecurityException("El formato hash ($type) no es valido.");
        }

        // Retorna el valor hasheado
        return $newHash;
    }
}
