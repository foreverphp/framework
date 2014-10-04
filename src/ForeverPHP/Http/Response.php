<?php
namespace ForeverPHP\Http;

use ForeverPHP\Http\HtmlResponse;
use ForeverPHP\Http\JsonResponse;

/**
 * Se encarga de devolver la respuesta adecuada al cliente.
 * solicitada.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.2.0
 */
class Response {
     //Los response_states deberian ir en otra parte ejemplo en Router o Constants
    private static $responseStates = array(
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error'
    );

    /**
     * Devuelve una respuesta en formato HTML.
     *
     * @param  string $template
     * @param  array  $context
     * @return ForeverPHP\Http\HtmlResponse
     */
    public static function make($template, $context = null) {
        return new HtmlResponse($template, $context);
    }

    /**
     * Devuelve una respuesta en formato JSON.
     *
     * @param  ForeverPHP\View\Context|array $context
     * @return ForeverPHP\Http\JsonResponse
     */
    public static function json($content) {
        if (!is_null($content)) {
            return new JsonResponse($content);
        }

        return false;
    }

    /**
     * Devuelve una descarga de archivo.
     *
     * @param  string $url
     * @return mixed
     */
    public static function download($url) {

    }

    public static function getResponseState($statusCode) {
        return static::$responseStates[$statusCode];
    }
}
