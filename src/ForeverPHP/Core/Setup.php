<?php
namespace ForeverPHP\Core;

use ForeverPHP\Core\Exceptions\SetupException;

/**
 * Importa objetos y configuraciones del framework.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 */
class Setup {
    /**
     * Carga una libreria al nucleo del framework.
     *
     * @param string $name  Nombre de la libreria.
     */
    public static function importLib($name) {
        $libPath = FOREVERPHP_ROOT . DS . 'libs' . DS . $name . '.php';

        if (file_exists($libPath)) {
            include_once $libPath;
        } else {
            throw new SetupException("La librería ($name) no existe.");
        }
    }

    /**
     * Carga un objeto de foreverPHP.
     *
     * @param string $name  Nombre del objeto a importar.
     */
    public static function import($name) {
        $importPath = FOREVERPHP_ROOT . DS . $name . '.php';

        if (file_exists($importPath)) {
            include_once $importPath;
        } else {
            throw new SetupException("El objeto a importar ($name) no existe.");
        }
    }

    /**
     * Permite definir valores, comprobando si ya existen previamente.
     *
     * @param string $define    Nombre de la constante a definir.
     * @param string $value     Valor de la constante.
     */
    public static function toDefine($define, $value) {
        if (!defined($define)) {
            define($define, $value);
        }
    }
}
