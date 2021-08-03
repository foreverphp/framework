<?php namespace ForeverPHP\Routing;

use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Core\Facades\Settings;
use ForeverPHP\Http\RedirectResponse;
use ForeverPHP\Http\Response;

/**
 * Permite la redireccion con multiples opciones.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since 0.2.0
 */
class Redirect
{
    /**
     * Redirecciona a una ruta especifica con un estado y encabezado
     * especificos.
     *
     * @param  string  $path
     * @param  integer $status
     * @param  array   $headers
     * @return \ForeverPHP\Http\RedirectResponse
     */
    public function to($path, $status = 301, $headers = array())
    {
        return $this->makeRedirect($path, $status, $headers);
    }

    /**
     * Redirecciona a una ruta segun su nombre asignado.
     *
     * @param  string $name
     * @return \ForeverPHP\Http\RedirectResponse
     */
    public function route($name)
    {
        // Debe construir una ruta segun el nombre de la ruta
    }

    /**
     * Redirecciona a un error, ejemplo un 404.
     *
     * @param  integer $errno
     * @return void
     */
    public function error($errno)
    {
        $response = new Response();

        header("HTTP/1.0 $errno " . $response->getResponseStatus($errno), true, $errno);

        /*
         * Retorna un Response para mostrar el mensaje de que algo salio mal
         * este solo se muestra cuando esta en produccion.
         */
        if (!Settings::inDebug()) {
            Settings::set('ForeverPHPTemplate', true);
            Context::set('errno', $errno);
            Context::set('message', 'Oops, al parecer algo salió mal.');
            $response->render('error')->make();
        }
    }

    public function makeRedirect($path, $status, $headers)
    {
        $redirect = new RedirectResponse($path, $status, $headers);

        return $redirect;
    }
}
