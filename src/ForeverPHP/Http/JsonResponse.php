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
     * Objeto de tipo Context.
     *
     * @var \ForeverPHP\View\Context|array
     */
    private $content;

    private $statusCode;

    public function __construct($content, $statusCode = 200) {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    public function render() {
        $data = array();

        if (is_array($this->content)) {
            $data = $this->content;
        } else {
            // Obtiene los datos del contexto
            if ($context instanceof Context) {
                $data = $context->all();
            }
        }

        header('HTTP/1.0 ' . $this->statusCode . ' ' . Response::getResponseState($this->statusCode), true, $this->statusCode);
        header('Content-type: application/json; charset: utf-8');
        header('Accept-Charset: utf-8');

        // Retorna los datos en formato JSON
        echo json_encode($data);
    }
}
