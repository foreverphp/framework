<?php

namespace ForeverPHP\Http;

/**
 * Interface base para los objetos que devuelven la respuesta
 * al cliente.
 *
 * @since   Version 0.4.0
 */
interface ResponseInterface
{
    public function make(): void;
}
