<?php

namespace ForeverPHP\Http;

use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Http\ResponseInterface;

/**
 * Genera respuestas en formato JSON al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.2.0
 */
class JsonResponse implements ResponseInterface
{
    /**
     * Objeto de tipo Context o array que sera convertido a JSON.
     *
     * @var \ForeverPHP\View\Context|array
     */
    private $content;

    /**
     * Codigo de estado de la respuesta.
     *
     * @var integer
     */
    private $statusCode;

    /**
     * Charset de la respuesta
     *
     * @var
     */
    private $charset;

    public function __construct($content, $statusCode = 200, $charset = 'utf-8')
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->charset = $charset;
    }

    public function make()
    {
        $data = [];

        $data = (is_array($this->content)) ? $this->content : Context::all();

        header('HTTP/1.0 ' . $this->statusCode . ' ' .
            Response::getResponseStatus($this->statusCode), true, $this->statusCode);
        header("Content-type: application/json; charset: {$this->charset}");
        header("Accept-Charset: {$this->charset}");

        // Comienza la captura del buffer de salida
        ob_start();

        // Retorna los datos en formato JSON
        echo json_encode($data);
    }
}
