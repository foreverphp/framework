<?php namespace ForeverPHP\Http\TemplateEngines;

use ForeverPHP\Core\Facades\App;
use ForeverPHP\Core\Facades\Storage;
use ForeverPHP\Core\Settings;
use ForeverPHP\Http\TemplateEngines\TemplateInterface;
use ForeverPHP\Security\CSRF;

/**
 * Chameleon es el motor estandar de rendereo de templates
 *
 * @since   Version 0.1.0
 */
class TemplateVarNotFound extends \Exception {}

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
        $regex = "#\{\% extends ('|\")([0-9A-Za-z\-_]*)('|\")(| from ('|\")([0-9A-Za-z\-_]*)('|\")) \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $result, PREG_SET_ORDER);

        $resultLength = count($result);

        if ($resultLength == 1) {
            $extendsFile = '';
            $extendsLength = count($result[0]);

            if ($extendsLength == 5) {
                $extendsFile = $this->templatesDir . $result[0][2] . '.html';
            } else if ($extendsLength == 8) {
                // Verifica si la App esta cargada, en settings.php
                if (App::exists($result[0][6])) {
                    $extendsFile = APPS_ROOT . DS . $result[0][6] . DS .
                                'Templates' . DS . $result[0][2] . '.html';
                }

            } else {
                return false;
            }

            unset($results);

            if (Storage::exists($extendsFile)) {
                $this->dataRenderBase = Storage::get($extendsFile);
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    private function includesTemplate() {
        $regex = "#\{\% include ('|\")([0-9A-Za-z\-_]*)('|\")(| from ('|\")([0-9A-Za-z\-_]*)('|\")) \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $include) {
                $includeFile = '';
                $templateFile = DS .'Templates' . DS;

                // Verifica si la App esta cargada, en settings.php
                $includeLength = count($include);

                if ($includeLength > 5) {
                    if (App::exists($include[6])) {
                        $templateFile = APPS_ROOT . DS . $include[6] . DS .
                                        'Templates' . DS . $include[2] . '.html';
                    }
                } else {
                    $templateFile = $this->templatesDir . $include[2] .'.html';
                }

                if (Storage::exists($templateFile)) {
                    $includeFile = Storage::get($templateFile);
                }

                $this->dataRender = str_replace($include[0], trim($includeFile), $this->dataRender);
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function blocksTemplate() {
        $regex = "#\{\% block ('|\")([0-9A-Za-z\-_]*)('|\") \%\}([\w|\t|\r|\W]*?)\{\% endblock \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $block) {
                $regexReplace = "#\{\% block " . $block[1] . $block[2] .
                                $block[3] . " \%\}\{\% endblock \%\}#";

                // Busco el bloque en el template base y lo reemplazo
                $this->dataRenderBase = preg_replace($regexReplace,
                                        trim($block[4]), $this->dataRenderBase);
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
        $regex = "#\{\% static ('|\")(.*?)('|\") \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) != 0) {
            //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

            foreach ($results as $static) {
                $regexReplace = "#\{\% static '" . $static[1] . "' \%\}#";
                //$static_file = $url_base . $this->static_dir . $static[1];
                $staticFile = $this->staticDir . $static[1];

                $this->dataRender = preg_replace($regexReplace, trim($staticFile), $this->dataRender);
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function urlsTemplate() {
        $regex = "#\{\% url ('|\")(.*?)('|\") \%\}#";
        $results = array();

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) != 0) {
            //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

            foreach ($results as $static) {
                $regexReplace = "#\{\% url '" . $static[1] . "' \%\}#";
                //$static_file = $url_base . $this->static_dir . $static[1];
                //$static_file = URL_BASE . $static[1];
                $staticFile = $static[1];

                $this->dataRender = preg_replace($regexReplace, trim($staticFile), $this->dataRender);
            }
        } else {
            return false;
        }

        unset($results);
        return true;
    }

    private function isVarLoop($var) {
        if (is_string($var)) {
            if (!preg_match('#\'([0-9A-Za-z\-_]+)\'#', $var)) {
                return true;
            }
        }

        return false;
    }

    private function setTypeVarLoop(&$var) {
        if (lower($var) === 'true') {
            $var = true;
        } else if (lower($var) === 'false') {
            $var = false;
        } else if (filter_var($var, FILTER_VALIDATE_INT)) {
            settype($var, 'int');
        } else if (filter_var($var, FILTER_VALIDATE_FLOAT)) {
            settype($var, 'float');
        } else if (lower($var) === 'null') {
            settype($var, 'null');
        } else {
            settype($var, 'string');
        }
    }

    private function inIf($data, $operands = 2) {
        $varNotFound = false;
        $withElse = false;
        $operador = '';

        // Operandos y operador
        if ($operands == 1 && count($data) == 4) {
            $var1 = $data[2];
            $operador = trim($data[1]);
        } else {
            $var1 = $data[1];
        }

        if ($operands == 2) {
            $var2 = $data[3];
            $operator = $data[2];
        }

        // Contenidos del if else endif
        $content1 = ($operands == 1) ? ((count($data) == 4) ? $data[3] : $data[2]) : $data[4];
        $content2 = '';

        // Verifica si hay un {% else %}
        $regex = "#([\w|\t|\r|\W]*?)\{\% else \%\}([\w|\t|\r|\W]*)#";
        $results = array();

        preg_match($regex, $content1, $results);

        if (count($results) > 0) {
            $content1 = $results[1];
            $content2 = $results[2];
            $withElse = true;
        }

        // Establece el tipo de las variables, por defecto son "string"
        $this->setTypeVarLoop($var1);

        // Verifica si $var_1 esta definida en las variables del template
        if ($this->isVarLoop($var1)) {
            if (array_key_exists($var1, $this->data)) {
                $var1 = $this->data[$var1];
            } else {
                $varNotFound = $var1;
            }
        }

        if ($operands == 2) {
            $this->setTypeVarLoop($var2);

            // Verifica si $var_2 esta definida en las variables del template
            if ($this->isVarLoop($var2)) {
                if (array_key_exists($var2, $this->data)) {
                    $var2 = $this->data[$var2];
                    $varNotFound = false;
                } else {
                    $varNotFound = $var2;
                }
            }
        }

        // Valido el operador para ver la accion a realizar
        $met = false;

        // Solo procesa el "if", si no hay errores en las variables
        if (!$varNotFound) {
            $var1 = $this->removeQuotes($var1);

            if ($operands == 1) {
                switch ($operador) {
                    case 'not': if (!$var1) { $met = true; } break;
                    default: if ($var1) { $met = true; } break;
                }
            } else if ($operands == 2) {
                $var2 = $this->removeQuotes($var2);

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
            }

            if ($met) {
                $this->dataRender = str_replace($data[0], trim($content1), $this->dataRender);
            } else {
                if ($withElse) {
                    $this->dataRender = str_replace($data[0], trim($content2), $this->dataRender);
                } else {
                    $this->dataRender = str_replace($data[0], '', $this->dataRender);
                }
            }
        } else {
            if (Settings::getInstance()->inDebug()) {
                throw new TemplateVarNotFound('The variable \'' . $varNotFound .
                                            '\' is not defined for template \'' .
                                            $this->template . '\'.');
            } else {
                $this->dataRender = str_replace($data[0], '', $this->dataRender);
            }
        }
    }

    private function ifsTemplate() {
        $regexSimple = "#\{\% if(| not) ([0-9A-Za-z\-_\.]*) \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        $regexDouble = "#\{\% if ([0-9A-Za-z\-_\.]*) (.*) ([0-9A-Za-z\-_\.'\"]*) \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        //$regexQuad = "#\{\% if ([0-9A-Za-z\-_\.]*) (.*) ([0-9A-Za-z\-_\.'\"]*) \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        //$regex_complex = "#\{\% if ([0-9a-zA-Z\-_]*) (.*) ([0-9a-zA-Z\-_]*) \%\}([\w|\t\|\r\|\W]*?)\{\% else \%\}([\w|\t\|\r\|\W]*?)\{\% endif \%\}#";
        $results = array();

        // Se buscan las apariciones de if de operando simple
        preg_match_all($regexSimple, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $if) {
                $this->inIf($if, 1);
            }
        }

        // Se buscan las apariciones de if de doble operando
        preg_match_all($regexDouble, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            foreach ($results as $if) {
                $this->inIf($if, 2);
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
                        $contentToChange =   str_replace($var[0], trim($value[$var[1]]), $contentToChange);
                    }

                    $contentFor .= $contentToChange;
                }

                $this->dataRender = str_replace($data[0], trim($contentFor), $this->dataRender);
            }

            unset($results);
        } else {
            // Si no se elimina la etiqueta for
            $this->dataRender = str_replace($data[0], trim(''), $this->dataRender);
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
        $regex = "#\{\% urlbase \%\}#";
        //$url_base = (URL_BASE === '/') ? '' : URL_BASE;

        $this->dataRender = preg_replace($regex, '/', $this->dataRender);

        // Tag url_static
        $regex = "#\{\% urlstatic \%\}#";

        $this->dataRender = preg_replace($regex, '/' . 'static/', $this->dataRender);
    }

    private function securityTagsTemplate() {
        $results = array();

        // Tag csrf_token
        $regex = "#\{\% csrftoken \%\}#";

        preg_match_all($regex, $this->dataRender, $results, PREG_SET_ORDER);

        if (count($results) > 0) {
            $token = CSRF::generateToken();
            $inputTag = '<input type="hidden" name="csrfToken" value="' . $token . '" />';

            $this->dataRender = preg_replace($regex, $inputTag, $this->dataRender);
        }

        unset($results);
    }

    private function loadTemplate() {
        if (Settings::getInstance()->get('ForeverPHPTemplate')) {
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

    /**
     * Permite minificar el resultado del render, incluye
     * la limpieza de comentarios HTML.
     *
     * @param  string $dataRender
     * @return string
     */
    private function minify($dataRender) {
        return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"),
                            array('', ' '),
                            str_replace(array("\n", "\r", "\t"), '', $dataRender));
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
        if (Settings::getInstance()->get('minifyTemplate') && !Settings::getInstance()->inDebug()) {
            $tplReady = $this->minify($tplReady);
        }

        $this->release();
        return $tplReady;
    }
}
