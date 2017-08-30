<?php namespace ForeverPHP\Http;

use ForeverPHP\Core\App;
use ForeverPHP\Core\Exceptions\SecurityException;
use ForeverPHP\Core\Facades\Cache;
use ForeverPHP\Core\Facades\Context;
use ForeverPHP\Core\Redirect;
use ForeverPHP\Core\Settings;
use ForeverPHP\Http\ResponseInterface;
use ForeverPHP\Http\TemplateEngines\Chameleon;
use ForeverPHP\Security\CSRF;

/**
 * Genera respuestas en formato HTML al cliente.
 *
 * @author  Daniel Nuñez S. <dnunez@emarva.com>
 * @since   Version 0.2.0
 */
class HtmlResponse implements ResponseInterface {
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

    public function __construct($template, $usingCache = false) {
        $this->template = $template;
        $this->usingCache = $usingCache;
    }

    public function make($returnRender = false) {
        $data = array();

        // Valido el token CSRF, el cual solo esta disponible en GET o POST
        //if (Settings::getInstance()->inDebug()) {
        if (Settings::getInstance()->exists('csrfToken')) {
            if (!CSRF::validateToken()) {
                throw new SecurityException('Access denied, invalid token. It becomes impossible to process your request to start or close this page.');
            }
        }
        /*} else {
            Redirect::toError(500);
        }*/

        // Obtienen los contextos
        $data = Context::all();

        // Se limpian los contextos
        Context::removeAll();

        // Valida si el render solo esta disponible en DEBUG
        //if ($only_debug) {
        //    Router::redirectToError(500);
        //} else {
            $tplEngine = Settings::getInstance()->get('templateEngine');

            if ($tplEngine == 'chameleon') {
                $tpl = new Chameleon();
            }

            // Comienza la captura del buffer de salida
            ob_start();

            // Se construye la ruta del template
            $templatesDir = '';
            $staticDir = '';
            $templatePath = '';
            $appAndTemplate = null;

            // Se definen las rutas de los templates y de los archivos estaticos
            if (Settings::getInstance()->get('ForeverPHPTemplate')) {
                // Se usaran templates de foreverPHP
                $templatesDir = FOREVERPHP_TEMPLATES_PATH;
                $staticDir = str_replace(DS, '/', FOREVERPHP_STATIC_PATH);
            } else {
                $templatesDir = TEMPLATES_PATH;
                $staticDir = str_replace(DS, '/', STATIC_PATH);
            }

            $tpl->setTemplatesDir($templatesDir);

            // Verifica si el template maneja aplicacion diferente y subdirectorios
            if (strpos($this->template, '@')) {
                $appAndTemplate = explode('@', $this->template);
            }

            if ($appAndTemplate != null) {
                $templatesDir = APPS_ROOT . DS . $appAndTemplate[0] . DS . 'Templates' . DS;
                $this->template = $appAndTemplate[1];
            }

            $subdirectories = explode('.', $this->template);
            $totalSubdirectories = count($subdirectories);

            if ($totalSubdirectories > 1) {
                $this->template = $subdirectories[$totalSubdirectories - 1];

                array_pop($subdirectories);

                foreach ($subdirectories as $subdirectory) {
                    $templatesDir .= $subdirectory . DS;
                }
            } else {
                $this->template = $subdirectories[0];
            }

            // Se define la ruta del template
            $templatePath = $templatesDir . $this->template;

            // Le indico al motor de templates la ruta de los archivos estaticos
            $tpl->setStaticDir($staticDir);

            // Renderea el template
            $render = $tpl->render($templatePath, $data);

            if (!$returnRender) {
                echo $render;
            }

            /*
             * Aca se controla el cache de templates.
             */
            if (ob_get_length() > 0) {
                if ($this->usingCache) {
                    // Obtiene el contenido del template renderizado
                    $cacheValue = ob_get_contents();

                    // POR AHORA SOLO GUARDA EL CACHE PARA PRUEBAS NO VALIDA DURACION, NI SI EXISTE
                    // Guarda el template en el cache
                    Cache::set($this->template . '.template.cache', $cacheValue);
                }
            }
        //}

        // Rendereo el template
        //echo $this->_template->render($template, $data);

        /*
         * Guardo en la configuracion el estado de la vista para evitar
         * conflictos, por ejemplo intentar acceder a la session despues de
         * haber rendereado.
         */
        // NOTA: al paracer esto ya no es necesario, validar despues
        Settings::getInstance()->set('viewState', 'render_ok');

        // Devuelve el template rendereado si el parametro $returnRender esta en true
        if ($returnRender) {
            return $render;
        }
    }
}
