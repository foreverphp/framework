<?php namespace ForeverPHP\View;

use ForeverPHP\Core\Setup;
use ForeverPHP\Core\Exceptions\ViewException;

/**
 * Todas las vistas deben heredar de este archivo, para ser tratadas
 * como tal.
 *
 * @since       Version 0.1.0
 */
class View {
    // Si se sobre escribe con valor true la vista se adaptara para trabajar con RESTful
    public $restful = false;

    public function __construct() {}

    /**
     * Importa ya sea modelos o vistas de la App en ejecución o
     * de otra.
     *
     * @param  string $type
     * @param  string $toImport
     */
    public function import($type, $toImport) {
        $pathToImport = '';

        // Valida si se esta importando de la misma App o de otra


        // Valida el tipo de objeto a importar
        if ($type === 'model') {
            $pathToImport = 'models/';
        } elseif ($type === 'view') {
            $pathToImport = 'views/';
        } else {
            throw new ViewException("Imported object type ($type) is invalid.");
        }

        Setup::importFromApp($pathToImport);
    }

    /*
     * Solo retorna el contexto global.
     */
    /*protected static function _get_global_context() {
        return self::$_global_contexts;
    }*/

    /**
     * Renderea un templete y lo retorna como respuesta al cliente.
     *
     * @param  string  $template    Nombre del template a renderizar.
     * @param  Context $context     Contexto de datos a combinar al template.
     * @return string               Retorna un string con el contenido HTML.
     */
/*    public function render_to_response($template, $context = null) {
        $data = array();

        // Valido el token CSRF, el cual solo esta disponible en GET o POST
        if (DEBUG) {
            if (Settings::exists('csrf_token')) {
                if (!CSRF::validate_token()) {
                    throw new SecurityException('Acceso denegado, token inválido. Es imposible procesar tu solicitud vuelve al inicio o cierra esta página.');
                    //return;
                }
            }
        } else {
            Router::redirect_to_500();
        }

        // Obtiene los datos del contexto
        if ($context != null) {
            if ($context instanceof Context) {
                $data = $context->all();
            }
        }

        // Valida si hay contextos globales y de ser asi se conbinan con el contexto
        $global_contexts = static::_get_global_context();

        if (count($global_contexts) > 0) {
            $data = array_merge($global_contexts, $data);
        }

        // Valida si el render solo esta disponible en DEBUG
        if ($only_debug) {
            Router::redirect_to_500();
        } else {
            $tpl = new Template();

            // Rendereo el template
            echo $tpl->render($template, $data);
        }

        // Rendereo el template
        echo $this->_template->render($template, $data);*/

        /*
         * Guardo en la configuracion el estado de la vista para evitar
         * conflictos, por ejemplo intentar acceder a la session despues de
         * haber rendereado.
         */
        // NOTA: al paracer esto ya no es necesario, validar despues
        /*Settings::set('view_state', 'render_ok');
    }*/

    /**
     * [render description]
     * @param  [type]  $template   [description]
     * @param  [type]  $context    [description]
     * @param  boolean $only_debug [description]
     * @return [type]              [description]
     */
    //public static function render($template, $context = null, $to_response = true) {
        /*$data = array();

        // Obtiene los datos del contexto
        if ($context != null) {
            if ($context instanceof Context) {
                $data = $context->all();
            }
        }

        // Valida si hay contextos globales y de ser asi se conbinan con el contexto
        $global_contexts = static::_get_global_context();

        if (count($global_contexts) > 0) {
            $data = array_merge($global_contexts, $data);
        }*/

        /*if ($only_debug) {
            Router::redirect_to_404();
        } else {
            $tpl = new Template();

            // Rendereo el template
            echo $tpl->render($template, $data);
        }*/
        /*if ($to_response) {
            return new ViewRender()->to_response();
        }

        return new ViewRender();*/
    //}

    /*public function render_to_json($context, $state_code = 200) {
        $data = array();
        $response = '';

        // Obtiene los datos del contexto
        if ($context instanceof Context) {
            $data = $context->all();
        }

        header('HTTP/1.0 ' . $state_code . ' ' . $this->_response_states[$state_code], true, $state_code);

        //if ($type == REST_DATA_TYPE_JSON) {
            header('Content-type: application/json; charset: utf-8');
            header('Accept-Charset: utf-8');
            $response = json_encode($data);
        //}

        echo $response;
    }*/

    /*public function render_to_csv() {

    }

    public function render_to_excel() {
        Setup::import('vendor/phpexcel/phpexcel');
    }

    public function render_to_pdf() {

    }*/
}
