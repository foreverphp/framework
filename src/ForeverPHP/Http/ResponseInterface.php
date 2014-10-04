<?php
namespace ForeverPHP\Http;

/**
 * Interface base para los objetos que devuelven la respuesta
 * al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.2.0
 */
interface ResponseInterface {
    public function render();
}
