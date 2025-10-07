<?php

namespace ForeverPHP\Routing;

use ForeverPHP\Core\Helpers\GlobalHelpers;
use ForeverPHP\Core\Settings;
use ForeverPHP\Http\RedirectResponse;
use ForeverPHP\Http\Response;
use ForeverPHP\View\Context;

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
    public function to($path, $status = 301, $headers = [])
    {
        return $this->makeRedirect($path, $status, $headers);
    }

    /**
     * Redirecciona a una ruta segun su nombre asignado.
     *
     * @param  string $name
     * @return \ForeverPHP\Http\RedirectResponse
     */
    //public function route($name)
    //{
    //    // Debe construir una ruta segun el nombre de la ruta
    //}

    /**
     * Redirecciona a un error, ejemplo un 404.
     *
     * @param  int $errno
     * @return void
     */
    public function error(int $errno)
    {
        $response = new Response();

        if (!$response::existsResponseStatus($errno)) {
            $errno = 500;
        }

        $responseStatus = $response::getResponseStatus($errno);

        header("HTTP/1.0 $errno $responseStatus", true, $errno);

        // Temporal para mostrar el error mientras implemento idiomas
        Settings::getInstance()->set('ForeverPHPTemplate', true);

        $lang = GlobalHelpers::getLanguage();
        Context::getInstance()->set('lang', $lang);

        // Templates de error disponibles
        $availableErrors = [400, 401, 403, 404, 429, 500, 502, 503];

        Context::getInstance()->set('errno', $errno);
        Context::getInstance()->set('responseStatus', $responseStatus);

        // Si el error no esta disponible se cambia a 0, para usar el template genérico de error
        if (!in_array($errno, $availableErrors)) {
            $errno = 0;
        }

        $response->render("error-pages.$lang.$errno")->make();

        /**
         * Retorna un Response para mostrar el mensaje de que algo salio mal
         * este solo se muestra cuando esta en produccion.
         */
        /*if (!Settings::getInstance()->inDebug()) {
            Context::getInstance()->set('errno', $errno);
            Context::getInstance()->set('errorTitle', GlobalHelpers::lang("errors.errorTitle$errno"));
            Context::getInstance()->set('errorMessage', GlobalHelpers::lang("errors.errorMessage$errno"));

            $response->render('error')->make();
        }*/
    }

    public function makeRedirect($path, $status, $headers)
    {
        $redirect = new RedirectResponse($path, $status, $headers);

        return $redirect;
    }
}
