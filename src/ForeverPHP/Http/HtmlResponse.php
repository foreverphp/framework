<?php

namespace ForeverPHP\Http;

use ForeverPHP\Core\Exceptions\SecurityException;
use ForeverPHP\Core\Facades\Cache;
use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Core\Settings;
use ForeverPHP\Http\ResponseInterface;
use ForeverPHP\Http\TemplateEngines\Chameleon;
use ForeverPHP\Security\CSRF;

/**
 * Genera respuestas en formato HTML al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.4.0
 */
class HtmlResponse implements ResponseInterface
{
    /**
     * Nombre del template a renderizar.
     *
     * @var string
     */
    private $template;

    /**
     * Indica si se debe usar cache en el renderizado.
     *
     * NOTA: La variable $usingCache no deberia ir en el contructor
     *       ya que al especificar en la configuracion que esta
     *       activo el cache la plantilla deberia usar cache, ver
     *       la mejor forma de usar el cache.
     *
     * @var bool
     */
    private $usingCache;

     /**
     * Codigo de estado de la respuesta.
     *
     * @var integer
     */
    private $statusCode;

    public function __construct($template, $statusCode = 200, $usingCache = false)
    {
        $this->template = $template;
        $this->statusCode = $statusCode;
        $this->usingCache = $usingCache;
    }

    /**
     * Genera HTML y lo envía al cliente.
     */
    public function make($returnRender = false)
    {
        $this->validateCsrf();

        // Obtienen los contextos
        $data = Context::all();

        // Se limpian los contextos
        Context::removeAll();

        $tpl = $this->getTemplateEngine();

         // Define rutas
        [$templatesDir, $staticDir, $templateName] = $this->resolveTemplatePath();

        $tpl->setTemplatesDir($templatesDir);
        $tpl->setStaticDir($staticDir);

        $render = $tpl->render($templateName, $data);

        http_response_code($this->statusCode);
        header('Content-Type: text/html; charset=utf-8');

        if ($this->usingCache) {
            Cache::set("{$this->template}.template.cache", $render);
        }

        Settings::getInstance()->set('viewState', 'render_ok');

        if ($returnRender) {
            return $render;
        }

        echo $render;
    }

    /**
     * Valida el token CSRF si está habilitado.
     */
    private function validateCsrf(): void
    {
        $settings = Settings::getInstance();
        if ($settings->exists('csrfToken') && !CSRF::validateToken()) {
            throw new SecurityException(
                'Access denied, invalid token. It becomes impossible to process your request.'
            );
        }
    }

    /**
     * Obtiene el motor de plantillas configurado.
     */
    private function getTemplateEngine(): object
    {
        $engine = Settings::getInstance()->get('templateEngine');
        return match ($engine) {
            'chameleon' => new Chameleon(),
            default => throw new \RuntimeException("Unknown template engine: {$engine}")
        };
    }

    /**
     * Resuelve la ruta física del template a renderizar.
     *
     * @return array [templatesDir, staticDir, templatePath]
     */
    private function resolveTemplatePath(): array
    {
        $settings = Settings::getInstance();
        $ds = safe_const('DS');

        $baseTemplatesDir = $settings->get('ForeverPHPTemplate')
            ? safe_const('FOREVERPHP_TEMPLATES_PATH', safe_const('TEMPLATES_PATH'))
            : safe_const('TEMPLATES_PATH');

        $staticDir = $settings->get('ForeverPHPTemplate')
            ? str_replace($ds, '/', safe_const('FOREVERPHP_STATIC_PATH', safe_const('STATIC_PATH')))
            : str_replace($ds, '/', safe_const('STATIC_PATH'));

        $templateName = $this->template;

        // Si tiene formato "app@template.subdir.file"
        if (str_contains($templateName, '@')) {
            [$app, $templateName] = explode('@', $templateName, 2);
            $baseTemplatesDir = safe_const('APPS_ROOT') . $ds . $app . $ds . 'Templates' . $ds;
        }

        $segments = explode('.', $templateName);
        $file = array_pop($segments);
        $dir = implode($ds, $segments);
        $templatePath = rtrim($baseTemplatesDir . $ds . $dir . $ds . $file, $ds);

        return [$baseTemplatesDir, $staticDir, $templatePath];
    }
}
