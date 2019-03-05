<?php namespace ForeverPHP\Http\TemplateEngines;

/**
 * Interface base para los motores de rendereo de templates.
 *
 * @since   Version 1.0.0
 */
interface TemplateEngineInterface
{
    public function render($template, $data);
}
