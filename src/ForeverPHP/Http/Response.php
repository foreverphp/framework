<?php namespace ForeverPHP\Http;

use ForeverPHP\Http\HtmlResponse;
use ForeverPHP\Http\JsonResponse;

/**
 * Se encarga de devolver la respuesta adecuada al cliente.
 * solicitada.
 *
 * @since       Version 0.2.0
 */
class Response
{
    /**
     * Contiene los textos de estados los cuales se recuperar con su
     * respectivo codigo.
     *
     * @var array
     */
    private static $responseStatus = array(
        100 => 'Continue',
        101 => 'Switching Protocol',
        102 => 'Processing (WebDAV)',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status (WebDAV)',
        208 => 'Multi-Status (WebDAV)',
        226 => 'IM Used (HTTP Delta encoding)',
        300 => 'Multiple Choice',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'unused',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity (WebDAV)',
        423 => 'Locked (WebDAV)',
        424 => 'Failed Dependency (WebDAV)',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected (WebDAV)',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );

    /**
     * Devuelve una respuesta del rendereo de un template.
     *
     * @param  string $template
     * @return \ForeverPHP\Http\HtmlResponse
     */
    public function render($template, $statusCode = 200)
    {
        return new HtmlResponse($template, $statusCode);
    }

    /**
     * Devuelve una respuesta en formato JSON.
     *
     * @param  \ForeverPHP\View\Context|array $context
     * @return \ForeverPHP\Http\JsonResponse
     */
    public function json($content, $statusCode = 200)
    {
        if (!is_null($content)) {
            return new JsonResponse($content, $statusCode);
        }

        return false;
    }

    /**
     * Devuelve una descarga de archivo.
     *
     * @param  string $url
     * @return mixed
     */
    public function download($url)
    {
        //
    }

    public static function getResponseStatus($status)
    {
        return static::$responseStatus[$status];
    }
}
