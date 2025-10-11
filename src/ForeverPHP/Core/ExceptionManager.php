<?php

namespace ForeverPHP\Core;

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
    private static $errors = [];

    /**
     * Almacena los errores manejados.
     * @var array
     */
    private static $handledErrors = [];

    /**
     * Flag para evitar recursión infinita
     * @var bool
     */
    private static $handling;

    private static function getErrorTypeAsString(int $type): string
    {
        return match ($type) {
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            //E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            default => 'E_UNKNOWN',
        };
    }

    private static function getErrorClass(int $type): string
    {
        $errorClass = 'error-exception';

        // Determinar el tipo y clase CSS según el código de error
        switch ($type) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $errorClass = 'error-fatal';
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $errorClass = 'error-warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $errorClass = 'error-notice';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $errorClass = 'error-deprecated';
                break;
            default:
                $errorClass = 'error-exception';
                break;
        }

        return $errorClass;
    }

    /**
     * Permite mostrar un excepción propia.
     *
     * @param int $type
     * @param array $errorsList
     * @return void
     */
    private static function viewException(array $errorsList)
    {
        if (static::$handling) {
            return;
        }

        static::$handling = true;

        $template = 'new-exception';

        if (Settings::getInstance()->inDebug()) {
            $contentBuffer = json_decode(ob_get_contents());

            // Limpio el buffer de salida previo
            if (ob_get_length()) {
                ob_clean();
            }

            // Contenido de la excepción
            $contentException = '';

            foreach ($errorsList as $error) {
                $contentException .= '<div class="error ' . static::getErrorClass($error['errno']) . '">';
                $contentException .= '    <div class="error-line">';
                $contentException .= '        <span class="label">Type:</span>';
                $contentException .= '        <span class="value type">' . $error['type'] . '</span>';
                $contentException .= '    </div>';

                if (!empty($error['code'])) {
                    $contentException .= '    <div class="error-line">';
                    $contentException .= '        <span class="label">Code:</span>';
                    $contentException .= '        <span class="value">' . $error['code'] . '</span>';
                    $contentException .= '    </div>';
                }

                $contentException .= '    <div class="error-line">';
                $contentException .= '        <span class="label">Message:</span>';
                $contentException .= '        <span class="value">' . $error['message'] . '</span>';
                $contentException .= '    </div>';
                $contentException .= '    <div class="error-line">';
                $contentException .= '        <span class="label">File:</span>';
                $contentException .= '        <span class="value file">' . $error['file'] . '</span>';
                $contentException .= '    </div>';
                $contentException .= '    <div class="error-line">';
                $contentException .= '        <span class="label">Line:</span>';
                $contentException .= '        <span class="value line">' . $error['line'] . '</span>';
                $contentException .= '    </div>';
                $contentException .= '    <div class="error-line">';
                $contentException .= '        <span class="label">Timestamp:</span>';
                $contentException .= '        <span class="value timestamp">' . $error['timestamp'] . '</span>';
                $contentException .= '    </div>';

                if (!empty($error['trace'])) {
                    $contentException .= '<div class="error-line">';
                    $contentException .= '    <span class="label">Stack Trace:</span>';
                    $contentException .= '    <div class="value">';
                    $contentException .=
                        '        <pre class="stack-trace">' . htmlspecialchars($error['trace']) . '</pre>';
                    $contentException .= '    </div>';
                    $contentException .= '</div>';
                }

                $contentException .= '</div>';
            }

            Context::useGlobal(false);
            Context::set('title', 'Error Log');
            Context::set('contentException', $contentException);

            $response = new Response();

            if (is_array($contentBuffer)) {
                $contentBuffer['ForeverPHPException'] = Context::all();
                $response->json($contentBuffer)->make();
            } else {
                // Si hay buffer de salida previo cambio el template
                if (ob_get_level() > 0 && ob_get_length() > 0) {
                    $template = 'exception-block';
                }

                // Le indico a la vista que haga render usando los templates del framework
                Settings::getInstance()->set('ForeverPHPTemplate', true);

                $response->render($template)->make();
            }
        } else {
            // Termino el buffer de salida y lo limpio de forma segura
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Redirijo a un error 500
            return Redirect::error(500);
        }

        static::$handling = false;
    }

    /**
     * Marca un error como manejado.
     * @param array $errorDetails
     * @return void
     */
    private static function markErrorAsHandled(array $errorDetails)
    {
        $signature = md5("{$errorDetails['message']}|{$errorDetails['file']}|{$errorDetails['line']}");
        static::$handledErrors[$signature] = true;

        // Actualizar el estado en el array de errores
        foreach (static::getErrors() as &$error) {
            if ($error['signature'] === $signature) {
                $error['handled'] = true;
            }
        }
    }

    /**
     * Verifica si un error ya fue manejado
     * @param string $signature
     * @return bool
     */
    private static function isErrorHandled($signature)
    {
        return isset(static::$handledErrors[$signature]);
    }

    /**
     * Manipulador de excepciones.
     *
     * @param \Throwable $e
     * @return void
     */
    public static function exceptionHandler(\Throwable $e)
    {
        $contentException = [
            'errno' => E_ERROR,
            'type' => 'E_ERROR',
            'code' => '',
            'message' => 'Invalid exception type.',
            'file' => '',
            'line' => '',
            'timestamp' => date('Y-m-d H:i:s', (int)microtime(true)),
            'trace' => ''
        ];

        /**
         * Primero se valida si viene el parametro $exception y que sea
         * de tipo Exception o herede de este.
         */
        if ($e !== null && $e instanceof \Throwable) {
            // Crear signature del error
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];

            $signature = md5("{$errorDetails['message']}|{$errorDetails['file']}|{$errorDetails['line']}");

            // Si ya fue manejado, no procesarlo nuevamente
            if (static::isErrorHandled($signature)) {
                return;
            }

            // Crear un mensaje más detallado
            $contentException['code'] = $e->getCode();
            $contentException['message'] = $e->getMessage();
            $contentException['file'] = $e->getFile();
            $contentException['line'] = $e->getLine();
            $contentException['trace'] = $e->getTraceAsString();

            // Marcar el error como manejado ANTES de procesarlo
            static::markErrorAsHandled($errorDetails);
        }

        static::viewException([$contentException]);
    }

    /**
     * Manipulador de errores, por ejemplo para controlar
     * errores fatales (E_ERROR).
     *
     * @param  int    $errno
     * @param  string $errstr
     * @param  string $errfile
     * @param  int    $errline
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // Si el error ya fue manejado, ignorarlo
        $errorSignature = md5("$errstr|$errfile|$errline");

        if (static::isErrorHandled($errorSignature)) {
            return true;
        }

        /**
         * Si la configuración "debugHideNotices" existe, indica si se
         * muestran o no los errores de tipo E_NOTICE.
         */
        if (Settings::getInstance()->exists('debugHideNotices')) {
            if ($errno == E_NOTICE && Settings::getInstance()->get('debugHideNotices')) {
                return true;
            }
        }

        // Agregar el error al array ANTES de marcarlo como manejado
        static::$errors[] = [
            'type' => static::getErrorTypeAsString($errno),
            'code' => '',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'handled' => false,
            'signature' => $errorSignature,
            'errno' => $errno,
            'timestamp' => microtime(true)
        ];

        // Marcar como manejado inmediatamente
        $errorDetails = [
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        static::markErrorAsHandled($errorDetails);

        // Para errores fatales, no lanzar excepción, solo registrar
        $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
        if (in_array($errno, $fatalErrors)) {
            return true; // No lanzar excepción para errores fatales
        }

        // Para otros tipos de errores, lanzar excepción
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Obtiene la lista de errores de forma segura
     * @return array
     */
    private static function getErrors(): array
    {
        return is_array(static::$errors) ? static::$errors : [];
    }

    /**
     * Obtiene solo los errores no manejados
     * @return array
     */
    private static function getUnhandledErrors(): array
    {
        return array_filter(static::getErrors(), fn($error) => !$error['handled']);
    }

    /**
     * Muestra los errores no manejados.
     *
     * @return void
     */
    private static function showErrors()
    {
        $unhandledErrors = static::getUnhandledErrors();

        if (count($unhandledErrors) > 0) {
            $errorsList = [];

            foreach ($unhandledErrors as $error) {
                $errorsList[] = [
                    'errno' => $error['errno'], // Ejemplo 2 = E_WARNING
                    'type' => $error['type'], // Ejemplo E_WARNING como string
                    'message' => htmlspecialchars($error['message']),
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'timestamp' => date('Y-m-d H:i:s', (int)$error['timestamp']),
                    'trace' => ''
                ];
            }

            static::viewException($errorsList);
        }
    }

    /**
     * Ultima función en ejecutarse, una vez terminada la ejecución del script.
     *
     * @return void
     */
    public static function shutdown()
    {
        // Verificar si hay errores no manejados
        $unhandledErrors = static::getUnhandledErrors();

        if (empty($unhandledErrors)) {
            $error = error_get_last();

            if ($error !== null) {
                $isFatal = in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);

                if ($isFatal) {
                    $signature = md5("{$error['message']}|{$error['file']}|{$error['line']}");

                    // Solo procesar si no fue manejado previamente
                    if (!static::isErrorHandled($signature)) {
                        // Iniciar buffer de salida para capturar cualquier output
                        if (ob_get_level() === 0) {
                            ob_start();
                        }

                        static::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
                        static::showErrors();
                        return; // No llamar ob_end_flush si ya mostramos errores
                    }
                }
            }
        } else {
            static::showErrors();
            return;
        }

        /**
         * Como ultima función en ejecutarse, es acá donde se termina el flujo
         * del buffer de salida y lo muestra - solo si no se mostraron errores.
         */
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    /**
     * Método para obtener estadísticas de errores (útil para debugging)
     * @return array
     */
    public static function getErrorStats(): array
    {
        $errors = static::getErrors();
        $handled = array_filter($errors, fn($error) => $error['handled']);
        $unhandled = array_filter($errors, fn($error) => !$error['handled']);

        return [
            'total' => count($errors),
            'handled' => count($handled),
            'unhandled' => count($unhandled),
            'errors' => $errors
        ];
    }

    /**
     * Limpia el registro de errores (útil para testing)
     * @return void
     */
    public static function clearErrors(): void
    {
        static::$errors = [];
        static::$handledErrors = [];
        static::$handling = false;
    }
}
