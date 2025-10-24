<?php

namespace ForeverPHP\Http;

use ForeverPHP\View\Context;

/**
 * Se encarga de devolver la respuesta adecuada al cliente.
 *
 * Proporciona métodos para renderizar HTML, devolver JSON, manejar descargas
 * y obtener textos de códigos de estado HTTP.
 *
 * @since Version 0.4.0
 */
class Response
{
    /**
     * Textos de estados HTTP mapeados por código.
     *
     * @var array<int,string>
     */
    private static $responseStatus = [
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
    ];

    /**
     * Renderiza un template HTML.
     *
     * @param string $template Nombre del template
     * @param int $statusCode Código de estado HTTP
     * @return HtmlResponse
     */
    public function render(string $template, int $statusCode = 200): HtmlResponse
    {
        return new HtmlResponse($template, $statusCode);
    }

    /**
     * Devuelve una respuesta en formato JSON.
     *
     * @param Context|array $content Contenido a devolver
     * @param int $statusCode Código de estado HTTP
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException Si $content es null
     */
    public function json(Context|array $content, int $statusCode = 200): JsonResponse
    {
        if ($content === null) {
            throw new \InvalidArgumentException('JSON content cannot be null.');
        }

        return new JsonResponse($content, $statusCode);
    }

    /**
     * Inicia la descarga de un archivo.
     *
     * @param string $filePath Ruta del archivo
     * @param string|null $filename Nombre opcional del archivo para descargar
     * @return void
     */
    public function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "File not found.";
            exit();
        }

        $filename ??= basename($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit();
    }

    /**
     * Obtiene el texto asociado a un código de estado HTTP.
     *
     * @param int $status Código de estado
     * @return string Texto del estado
     */
    public static function getResponseStatus(int $status): string
    {
        // Si el status no esta en el array de estados devuelve 500
        return static::$responseStatus[$status] ?? static::$responseStatus[500];
    }

    /**
     * Verifica si un código de estado HTTP existe.
     *
     * @param int $status Código de estado
     * @return bool
     */
    public static function existsResponseStatus(int $status): bool
    {
        // Si el status no esta en el array de estados devuelve 500
        return isset(static::$responseStatus[$status]);
    }
}
