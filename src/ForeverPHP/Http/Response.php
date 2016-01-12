<?php namespace ForeverPHP\Http;

use ForeverPHP\Http\HtmlResponse;
use ForeverPHP\Http\JsonResponse;

/**
 * Se encarga de devolver la respuesta adecuada al cliente.
 * solicitada.
 *
 * @since       Version 0.2.0
 */
class Response {
    /**
     * Contiene los textos de estados los cuales se recuperar con su
     * respectivo codigo.
     *
     * @var array
     */
    private static $responseStatus = array(
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
     * Devuelve una respuesta del rendereo de un template.
     *
     * @param  string $template
     * @return \ForeverPHP\Http\HtmlResponse
     */
    public function render($template) {
        return new HtmlResponse($template);
    }

    /**
     * Devuelve una respuesta en formato JSON.
     *
     * @param  \ForeverPHP\View\Context|array $context
     * @return \ForeverPHP\Http\JsonResponse
     */
    public function json($content) {
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
    public function download($url) {

    }

    public static function getResponseStatus($status) {
        return static::$responseStatus[$status];
    }
}
