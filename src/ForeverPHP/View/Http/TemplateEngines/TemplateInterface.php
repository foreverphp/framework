<?php

namespace ForeverPHP\View\Http\TemplateEngines;

/**
 * Interface base para los motores de rendereo de templates.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.2.0
 */
interface TemplateInterface
{
    public function render($template, $data);
}
