<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static \ForeverPHP\Http\HtmlResponse render(string $template, int $statusCode = 200)
 * @method static \ForeverPHP\Http\JsonResponse json(\ForeverPHP\View\Context|array $context, int $statusCode = 200)
 * @method static void download(string $url, ?string $filename = null)
 * @method static string getResponseStatus(int $status)
 * @method static bool existsResponseStatus(int $status)
 * @see \ForeverPHP\Http\Response
 */
class Response extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return new \ForeverPHP\Http\Response();
    }
}
