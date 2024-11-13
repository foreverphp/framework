<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static \ForeverPHP\Http\RedirectResponse to(strig $path, int $status = 301, array $headers = [])
 * @method static \ForeverPHP\Http\RedirectResponse route(string $name)
 * @method static void error(int $errno)
 * @method static \ForeverPHP\Http\RedirectResponse makeRedirect(strig $path, int $status = 301, array $headers = [])
 *
 * @see \ForeverPHP\Routing\Redirect
 */
class Redirect extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return new \ForeverPHP\Routing\Redirect();
    }
}
