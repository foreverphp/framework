<?php namespace ForeverPHP\Security;

use ForeverPHP\Core\Exceptions\SecurityException;

/**
 * Permite generar diversos tipos de hash.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class Hash {
    private static function generateMD5($value) {
        return md5($value);
    }

    private static function generateSHA1($value) {

    }

    public static function create($values, $type = 'md5') {
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
            $newHash = self::generateMD5($valueToHash);
        } elseif ($type === 'sha1') {
            $newHash = self::generateSHA1($valueToHash);
        } else {
            // Formato no compatible
            throw new SecurityException("El formato hash ($type) no es valido.");
        }

        // Retorna el valor hasheado
        return $newHash;
    }
}
