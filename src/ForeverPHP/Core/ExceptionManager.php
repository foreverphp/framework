<?php namespace ForeverPHP\Core;

use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Core\Facades\Redirect;
use ForeverPHP\Http\Response;

/**
 * Controla todos los errores producidos, en modo Debug lanza mensajes
 * comprensibles para el desarrollador y en producción un error 500.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class ExceptionManager
{
    /**
     * Almacena la pila de errores.
     *
     * @var string
     */
    private static $errors = array();

    /**
     * Permite mostrar un excepción propia.
     *
     * @param  string $type
     * @param  string $message
     * @return void
     */
    private static function viewException($type, $message)
    {
        $template = 'exception';
        $title = 'Excepción';

        // 1 es Error
        if ($type === 1) {
            $title = 'Error';
        }

        if (Settings::getInstance()->inDebug()) {
            $contentBuffer = json_decode(ob_get_contents());

            // Limpio el buffer de salida previo
            if (ob_get_length()) {
                ob_clean();
            }

            Context::useGlobal(false);
            Context::set('exception', $title);
            Context::set('details', $message);

            $response = new Response();

            if (is_array($contentBuffer)) {
                $contentBuffer['ForeverPHPException'] = Context::all();

                $response->json($contentBuffer)->make();
            } else {
                // Si hay buffer de salida previo cambio el template
                if (ob_get_length() != 0) {
                    $template = 'exception-block';
                }

                // Le indico a la vista que haga render usando los templates del framework
                Settings::getInstance()->set('ForeverPHPTemplate', true);

                $response->render($template)->make();
            }
        } else {
            // Termino el buffer de salida y lo limpio
            ob_end_clean();

            // Redirijo a un error 500
            return Redirect::error(500);
        }
    }

    /**
     * Manipulador de excepciones.
     *
     * @param  Exception $exception
     * @return void
     */
    public static function exceptionHandler($exception)
    {
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

        static::viewException(0, $message);
    }

    /**
     * Manipulador de errores, por ejemplo para controlar
     * errores fatales (E_ERROR).
     *
     * @param  int    $errno
     * @param  string $errstr
     * @param  string $errfile
     * @param  int    $errline
     * @return void
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        /*
         * Si la configuración "debugHideNotices" existe, indica si se
         * muestran o no los errores de tipo E_NOTICE.
         */
        if (Settings::getInstance()->exists('debugHideNotices')) {
            if ($errno == E_NOTICE && Settings::getInstance()->get('debugHideNotices')) {
                return;
            }
        }

        switch ($errno) {
            case E_ERROR: // 1
                $type = 'E_ERROR';
                break;
            case E_WARNING: // 2
                $type = 'E_WARNING';
                break;
            case E_PARSE: // 4
                $type = 'E_PARSE';
                break;
            case E_NOTICE: // 8
                $type = 'E_NOTICE';
                break;
            case E_CORE_ERROR: // 16
                $type = 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING: // 32
                $type = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR: // 64
                $type = 'E_COMPILE_ERROR';
                break;
            case E_CORE_WARNING: // 128
                $type = 'E_COMPILE_WARNING';
                break;
            case E_USER_ERROR: // 256
                $type = 'E_USER_ERROR';
                break;
            case E_USER_WARNING: // 512
                $type = 'E_USER_WARNING';
                break;
            case E_USER_NOTICE: // 1024
                $type = 'E_USER_NOTICE';
                break;
            case E_STRICT: // 2048
                $type = 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR: // 4096
                $type = 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED: // 8192
                $type = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED: // 16384
                $type = 'E_USER_DEPRECATED';
                break;
        }

        array_push(static::$errors, array(
            'type' => $type,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ));
    }

    /**
     * Muestra los errores.
     *
     * @return void
     */
    private static function showErrors()
    {
        if (count(static::$errors) > 0) {
            $errorsList = '';

            foreach (static::$errors as $error) {
                $errorsList .= 'Tipo: ' . $error['type'] . '<br>';
                $errorsList .= 'Mensaje: ' . $error['message'] . '<br>';
                $errorsList .= 'Archivo: ' . $error['file'] . '<br>';
                $errorsList .= 'Line: ' . $error['line'] . '<br><br>';
            }

            static::viewException(1, $errorsList);
        }
    }

    /**
     * Ultima función en ejecutarse, una vez terminada la ejecución del script.
     *
     * @return void
     */
    public static function shutdown()
    {
        if (count(static::$errors) == 0) {
            $error = error_get_last();
            $isFatal = in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);

            if (!is_null($error) && $isFatal) {
                ob_start();

                static::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
            }
        }

        // Muestra los errores
        //static::showErrors();

        /*
         * Como ultima funcion en ejecutarse, es aca donde se termina el flujo
         * del buffer de salida y lo muestra.
         */
        ob_end_flush();
    }
}
