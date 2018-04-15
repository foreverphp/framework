<?php namespace ForeverPHP\Routing;

use ForeverPHP\Core\Facades\Settings;
use ForeverPHP\Http\{RedirectResponse, Response};
use ForeverPHP\View\Context;

/**
 * Permite la redireccion con multiples opciones.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since 1.0.0
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
    public function to(string $path, int $status = 301, array $headers = array()) {
        return $this->makeRedirect($path, $status, $headers);
    }

    /**
     * Redirecciona a una ruta segun su nombre asignado.
     *
     * @param  string $name
     * @return \ForeverPHP\Http\RedirectResponse
     */
    public function route(string $name) {
        // Debe construir una ruta segun el nombre de la ruta
    }

    /**
     * Redirecciona a un error, ejemplo un 404.
     *
     * @param  integer $errno
     * @return void
     */
    public function error(int $errno) {
        $response = new Response();

        header("HTTP/1.0 $errno " . $response->getResponseStatus($errno), true, $errno);

        /*
         * Retorna un Response para mostrar el mensaje de que algo salio mal
         * este solo se muestra cuando esta en produccion.         *
         */
        if (!Settings::inDebug()) {
            Settings::set('ForeverPHPTemplate', true);
            $response->render('error', new Context(array('message' => 'Oops, al parecer algo salió mal.')))->make();
        }
    }

    public function makeRedirect(string $path, int $status, array $headers) {
        $redirect = new RedirectResponse($path, $status, $headers);

        return $redirect;
    }
}
