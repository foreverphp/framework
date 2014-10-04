<?php namespace ForeverPHP\Http\TemplateEngines;

use ForeverPHP\Core\Settings;
use ForeverPHP\Http\TemplateEngines\TemplateInterface;
use ForeverPHP\Security\CSRF;

/**
 * Chameleon es el motor estandar de rendereo de templates
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.1.0
 */
class Chameleon implements TemplateInterface {
    private $templatesDir = '';
    private $staticDir = '';
    private $template = '';
    private $data = array();
    private $dataRenderBase = '';
    private $dataRender = '';

    private function removeQuotes($data) {
        $dataTemp = str_replace("'", '', $data);
        $dataTemp = str_replace('"', '', $dataTemp);
        return $dataTemp;
    }

    private function extendsTemplate() {
        $regex = "#\{\% extends ([0-9A-Za-z\-_]*) \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            $this->dataRenderBase = file_get_contents($this->templatesDir . $results[0][1] . '.html');
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function includesTemplate() {
        $regex = "#\{\% include ([0-9A-Za-z\-_]*) \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $include) {
                $regexReplace = "#\{\% include " . $include[1] . " \%\}#";
                $includeFile = file_get_contents($this->templatesDir . $include[1] . '.html');
                //echo $include_file;
                //echo $this->data_render_base;
                //echo $this->data_render;
                $this->dataRender = preg_replace($regexReplace, $includeFile, $this->dataRender);
                    //echo $this->data_render;
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function blocksTemplate() {
        $regex = "#\{\% block ([0-9A-Za-z\-_]*) \%\}([\w|\t|\r|\W]*?)\{\% endblock \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $bloque) {
                $regexReplace = "#\{\% block " . $bloque[1] . " \%\}\{\% endblock \%\}#";

                // Busco el bloque en el template base y lo reemplazo
                $this->dataRenderBase = preg_replace($regexReplace, $bloque[2], $this->dataRenderBase);
            }
        } else {
            // Cuando un template extiende otro debe de existir al menos un bloque
            // si no se produce una falla
            return false;
        }

        unset($results);
        return true;
    }

    private function staticsTemplate() {
        $regex = "#\{\% static '(.*?)' \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) != 0) {
            //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

            foreach ($results as $static) {
                $regexReplace = "#\{\% static '" . $static[1] . "' \%\}#";
                //$static_file = $url_base . $this->static_dir . $static[1];
                $staticFile = $this->staticDir . $static[1];

                $this->dataRender = preg_replace($regexReplace, $staticFile, $this->dataRender);
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function urlsTemplate() {
        $regex = "#\{\% url '(.*?)' \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) != 0) {
            //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

            foreach ($results as $static) {
                $regexReplace = "#\{\% url '" . $static[1] . "' \%\}#";
                //$static_file = $url_base . $this->static_dir . $static[1];
                //$static_file = URL_BASE . $static[1];
                $staticFile = $static[1];

                $this->dataRender = preg_replace($regexReplace, $staticFile, $this->dataRender);
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function inIf($data) {
        // Operandos y operador
        $var1 = $this->removeQuotes($data[1]);
        $var2 = $this->removeQuotes($data[3]);
        $operator = $data[2];

        // Contenidos del if else endif
        $content1 = $data[4];
        $content2 = '';

        // Verifica si hay un {% else %}
        $regex = "#([\w|\t|\r|\W]*?)\{\% else \%\}([\w|\t|\r|\W]*)#";
        $results = array();

        preg_match($regex, $content1, $results);

        if (count($results) > 0) {
            $content1 = $results[1];
            $content2 = $results[2];
        }

        // Verifica si $var_1 esta definida en las variables del template
        if (array_key_exists($var1, $this->data)) {
            $var1 = $this->data[$var1];
        }

        // Verifica si $var_2 esta definida en las variables del template
        if (array_key_exists($var2, $this->data)) {
            $var2 = $this->data[$var2];
        }

        // Valido el operador para ver la accion a realizar
        $met = false;

        switch ($operator) {
            case '==': if ($var1 == $var2) { $met = true; } break;
            case '===': if ($var1 === $var2) { $met = true; } break;
            case '!=': if ($var1 != $var2) { $met = true; } break;
            case '!==': if ($var1 !== $var2) { $met = true; } break;
            case '>': if ($var1 > $var2) { $met = true; } break;
            case '<': if ($var1 < $var2) { $met = true; } break;
            case '>=': if ($var1 >= $var2) { $met = true; } break;
            case '<=': if ($var1 <= $var2) { $met = true; } break;
        }

        if ($met) {
            $this->dataRender = str_replace($data[0], $content1, $this->dataRender);
        } else {
            $this->dataRender = str_replace($data[0], $content2, $this->dataRender);
        }
    }

    private function ifsTemplate() {
        $regexSimple = "#\{\% if ([0-9A-Za-z\-_]*) (.*) ([0-9A-Za-z\-_'\"]*) \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        //$regex_complex = "#\{\% if ([0-9a-zA-Z\-_]*) (.*) ([0-9a-zA-Z\-_]*) \%\}([\w|\t\|\r\|\W]*?)\{\% else \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        $results = array();

        // Se buscan las apariciones de if simples
        preg_match_all($regexSimple, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $if) {
                $this->inIf($if);
            }
        }

        unset($results);
    }

    private function inFor($data) {
        $arrayExpr = $data[1];
        $valueVar = $data[2];
        $content = $data[3];

        // Se valida que exista el $array_expr en el $this->data para seguir
        if (array_key_exists($arrayExpr, $this->data)) {
            $regex = "#\{\{" . $valueVar . ".([0-9A-Za-z\-_]*)\}\}#";
            $regex2 = "#\{\{([0-9A-Za-z\-_.]*)\}\}#";
            $results = array();

            preg_match_all($regex, $content, $results, PREG_SET_ORDER);

            if (count($results) > 0) {
                $contentFor = '';

                foreach ($this->data[$arrayExpr] as $key => $value) {
                    $contentToChange = $content;

                    foreach ($results as $vars => $var) {
                        $contentToChange =   str_replace($var[0], $value[$var[1]], $contentToChange);
                    }

                    $contentFor .= $contentToChange;
                }

                $this->dataRender = str_replace($data[0], $contentFor, $this->dataRender);
            }

            unset($results);
        } else {
            // Si no se elimina la etiqueta for
            $this->dataRender = str_replace($data[0], '', $this->dataRender);
        }
    }

    private function forsTemplate() {
        $regex = "#\{\% for ([0-9A-Za-z\-_]*) as ([0-9A-Za-z\-_]*) \%\}([\w|\t|\r|\W]*?)\{\% endfor \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $for) {
                $this->inFor($for);
            }
        }

        unset($results);
    }

    private function varsTemplate() {
        $regex = "#\{\{([0-9A-Za-z\-_]*)\}\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        // Reemplazo las variables por contenido
        if (count($results) != 0) {
            foreach($results as $var) {
                $this->dataRender = str_replace($var[0], $this->data[$var[1]], $this->dataRender);
            }
        }

        unset($results);
    }

    // FUNCION OBSOLETA
    private function routeTagsTemplate() {
        // Tag url_base
        $regex = "#\{\% url_base \%\}#";
        //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

        $this->dataRender = preg_replace($regex, '/', $this->dataRender);

        // Tag url_static
        $regex = "#\{\% url_static \%\}#";

        $this->dataRender = preg_replace($regex, '/' . 'static/', $this->dataRender);
    }

    private function securityTagsTemplate() {
        $results = array();

        // Tag csrf_token
        $regex = "#\{\% csrf_token \%\}#";

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            $token = CSRF::generateToken();
            $inputTag = '<input type="hidden" name="csrf_token" value="' . $token . '" />';

            $this->dataRender = preg_replace($regex, $inputTag, $this->dataRender);
        }

        unset($results);
    }

    private function loadTemplate() {
        if (Settings::get('ForeverPHPTemplate')) {
            // Se usaran templates de foreverPHP
            $this->templatesDir = FOREVERPHP_TEMPLATES_PATH;
            $this->staticDir = str_replace(DS, '/', FOREVERPHP_STATIC_PATH);
        } else {
            $this->templatesDir = TEMPLATES_PATH;
            $this->staticDir = str_replace(DS, '/', STATIC_PATH);
        }

        // Cargo el contenido del template
        $this->dataRender = file_get_contents($this->templatesDir . $this->template . '.html');

        // Verifica si el template extiende otro
        if ($this->extendsTemplate()) {
            // Verifica los bloques ya que el template extiende otro
            if ($this->blocksTemplate()) {
                $this->dataRender = $this->dataRenderBase;
            } else {
                return false;
            }
        }

        // Veifica la aparicion de inclusiones
        $this->includesTemplate();

        // Se establecen las rutas de los estaticos y url en general
        $this->staticsTemplate();
        $this->urlsTemplate();

        // Verifica la aparicion de bucles if
        $this->ifsTemplate();

        // Verifica la aparicion de bucles for
        $this->forsTemplate();

        // Verifica la aparicion de variables
        $this->varsTemplate();

        // Verifica la aparicion de tags de ruta
        $this->routeTagsTemplate();

        // Verifica la aparicion de tags de seguridad
        $this->securityTagsTemplate();

        return true;
    }

    private function release() {
        unset($this->dataRenderBase);
        unset($this->dataRender);
    }

    /**
     * Realiza el rendereo del template.
     *
     * @param  string $template Nombre del template a compilar.
     * @param  array  $data     Matriz de datos a conbinar con el template.
     * @return string           Retorna el texto HTML ya procesado.
     */
    public function render($template, $data) {
        $tplReady = '';
        $this->template = $template;
        $this->data = $data;

        if ($this->loadTemplate()) {
            $tplReady = $this->dataRender;
        }

        // Se verifica si hay que minificar el resultado
        if (Settings::get('minifyTemplate') && !Settings::inDebug()) {
            //$tpl_ready = str_replace("\n", ' ', $tpl_ready);
            //$tpl_ready = ereg_replace('[[:space:]]+', ' ', $tpl_ready);
            $tplReady = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $tplReady));
        }

        $this->release();
        return $tplReady;
    }
}
