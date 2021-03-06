<?php namespace ForeverPHP\Core;

use ForeverPHP\Core\Exceptions\SetupException;

/**
 * Importa objetos y configuraciones del framework y Apps.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 1.0.0
 */
class Setup {
    /**
     * Inicializador, se encarga de cargar los requerimientos para la
     * ejecución.
     *
     * @return void
     */
    public static function initialize() {
        /*
         * Defino la ruta de los templates y estaticos del framework.
         */
        static::toDefine('FOREVERPHP_ROOT', dirname(dirname(__FILE__)));
        static::toDefine('FOREVERPHP_TEMPLATES_PATH', FOREVERPHP_ROOT . DS . 'Static' . DS . 'templates' . DS);
        static::toDefine('FOREVERPHP_STATIC_PATH', basename(FOREVERPHP_ROOT) . DS . 'Static' . DS);

        /*
         * Establece como el manejador de errores ExceptionManager.
         */
        set_error_handler('\ForeverPHP\Core\ExceptionManager::errorHandler');

        /*
         * Establece como el manejador de excepciones no controladas a
         * ExceptionManager.
         */
        set_exception_handler('\ForeverPHP\Core\ExceptionManager::exceptionHandler');

        /*
         * Le deja el control de la función de cierre a ExceptionManager.
         */
        register_shutdown_function('\ForeverPHP\Core\ExceptionManager::shutdown');
    }

    /**
     * Carga una libreria al nucleo del framework.
     *
     * @param string $name
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
     * @param string $name
     */
    public static function import($name) {
        $importPath = FOREVERPHP_ROOT . DS . $name . '.php';

        if (file_exists($importPath)) {
            include_once $importPath;
        } else {
            throw new SetupException("The object to import ($name) not exists.");
        }
    }

    /**
     * Carga un objeto desde las Apps.
     *
     * @param string $path
     */
    public static function importFromApp($path) {
        $importPath = APPS_ROOT . DS . $path . '.php';

        if (file_exists($importPath)) {
            include_once $importPath;
        } else {
            throw new SetupException("The object to import ($path) not exists.");
        }
    }

    /**
     * Permite definir valores, comprobando si ya existen
     * previamente.
     *
     * @param string $define
     * @param string $value
     */
    public static function toDefine($define, $value) {
        if (!defined($define)) {
            define($define, $value);
        }
    }
}
