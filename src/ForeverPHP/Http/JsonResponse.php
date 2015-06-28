<?php namespace ForeverPHP\Http;

use ForeverPHP\Http\ResponseInterface;
use ForeverPHP\View\Context;

/**
 * Genera respuestas en formato JSON al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.2.0
 */
class JsonResponse implements ResponseInterface {
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

    public function __construct($content, $charset = 'utf-8', $statusCode = 200) {
        $this->content = $content;
        $this->charset = $charset;
        $this->statusCode = $statusCode;
    }

    public function make() {
        $data = array();

        if (is_array($this->content)) {
            $data = $this->content;
        } else {
            // Obtiene los datos del contexto
            if ($context instanceof Context) {
                $data = $context->all();
            }
        }

        header('HTTP/1.0 ' . $this->statusCode . ' ' . Response::getResponseStatus($this->statusCode), true, $this->statusCode);
        header('Content-type: application/json; charset: ' . $this->charset);
        header('Accept-Charset: ' . $this->charset);

        // Comienza la captura del buffer de salida
        ob_start();

        // Retorna los datos en formato JSON
        echo json_encode($data);
    }
}
