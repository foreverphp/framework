<?php namespace ForeverPHP\Routing;

use ForeverPHP\Http\RedirectResponse;
use ForeverPHP\Http\Response;

/**
 * Permite la redireccion con multiples opciones.
 *
 * @since 0.2.0
 */
class Redirect {
    /**
     * Redirecciona a una ruta especifica con un estado y encabezado
     * especificos.
     *
     * @param  string  $path
     * @param  integer $status
     * @param  array   $headers
     * @return \ForeverPHP\Http\RedirectResponse
     */
    public function to($path, $status = 301, $headers = array()) {
        return $this->makeRedirect($path, $status, $headers);
    }

    /**
     * Redirecciona a una ruta segun su nombre asignado.
     *
     * @param  string $name
     * @return \ForeverPHP\Http\RedirectResponse
     */
    public function route($name) {
        // Debe construir una ruta segun el nombre de la ruta
    }

    /**
     * Redirecciona a un error, ejemplo un 404.
     *
     * @param  integer $errno
     * @return void
     */
    public function error($errno) {
        $response = new Response();

        header("HTTP/1.0 $errno " . $response->getResponseStatus($errno), true, $errno);
    }

    public function makeRedirect($path, $status, $headers) {
        $redirect = new RedirectResponse($path, $status, $header);

        return $redirect;
    }
}