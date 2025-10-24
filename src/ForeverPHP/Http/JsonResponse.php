<?php

namespace ForeverPHP\Http;

use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Http\ResponseInterface;

/**
 * Genera respuestas en formato JSON al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
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

    /**
     * Genera y envía la respuesta JSON al cliente.
     *
     * @return void
     */
    public function make(): void
    {
        // Determinar los datos a codificar
        $data = is_array($this->content)
            ? $this->content
            : [];

        // Establecer código de estado HTTP
        http_response_code($this->statusCode);

        // Establecer encabezados
        header("Content-Type: application/json; charset={$this->charset}");

        // Codificar JSON con opciones modernas
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Manejo de errores en codificación
        if ($json === false) {
            $error = json_last_error_msg();
            http_response_code(500);
            $json = json_encode(['error' => "Error encoding JSON: {$error}"], JSON_UNESCAPED_UNICODE);
        }

        // Enviar el cuerpo de la respuesta
        echo $json;
    }
}
