<?php namespace ForeverPHP\Core;

use ForeverPHP\Core\Facades\Redirect;
use ForeverPHP\Http\Response;
use ForeverPHP\View\Context;

/**
 * Controla todos los errores producidos, en modo Debug lanza mensajes
 * comprensibles para el desarrollador y en producción un error 500.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class ExceptionManager {
    /**
     * Permite lanzar una excepcion propia se le puede pasar como
     * parametro una Exception.
     *
     * @param Exception $exception
     */
    private static function triggerException($exception = null) {
        $message = 'Tipo de excepción no valida.';

        /*
         * Primero se valida si viene el parametro $exception y que sea
         * de tipo Exception o herede de este.
         */
        if ($exception != null) {
            if ($exception instanceof \Exception) {
                // Crear un mensaje mas detallado
                $message = 'Message: ' . $exception->getMessage() . '<br />';
                $message .= 'Previus: ' . $exception->getPrevious() . '<br />';
                $message .= 'Code: ' . $exception->getCode() . '<br />';
                $message .= 'File: ' . $exception->getFile() . '<br />';
                $message .= 'Line: ' . $exception->getLine() . '<br />';
                $message .= 'Trace: ' . $exception->getTraceAsString() . '<br />';
            }
        }

        if (Settings::getInstance()->inDebug()) {
            $ctx = new Context();
            $ctx->set('exception', 'Excepción');
            $ctx->set('details', $message);

            // Le indico a la vista que haga render usando los templates del framework
            Settings::getInstance()->set('ForeverPHPTemplate', true);

            $response = new Response();
            $response->render('exception', $ctx)->make();
        } else {
            Redirect::error(500);
        }
    }

    public static function exceptionHandler($exception) {
        self::triggerException($exception);
    }
}
