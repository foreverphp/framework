<?php namespace ForeverPHP\Http;

use \ForeverPHP\Session\SessionManager;

/**
 * Devuelve una respuesta de tipo de redireccion.
 *
 * @since 0.3.0
 */
class RedirectResponse implements ResponseInterface {
    /**
     * Ruta a la cual redireccionar.
     *
     * @var string
     */
    private $path;

    /**
     * Codigo de estado.
     *
     * @var integer
     */
    private $status;

    /**
     * Encabezados a incluir en la redireccion.
     *
     * @var array
     */
    private $headers;

    /**
     * Almacena la instancia unica del administrador de sesiones.
     *
     * @var \ForeverPHP\Session\SessionManager
     */
    private $session;

    public function __construct($path, $status, $headers) {
        $this->path = $path;
        $this->status = $status;
        $this->headers = $headers;

        // Obtiene la instancia del administrador de sesiones
        $this->session = SessionManager::getInstance();
    }

    public function with($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->with($k, $value);
            }
        } elseif ($key instanceof \ForeverPHP\View\Context) {
            $this->with($key->all());
        } else {
            $this->session->set($key, $value, 'redirect');
        }

        return $this;
    }

    /**
     * Construye la redireccion
     *
     * @return void
     */
    public function make() {
        /*
         * Se guardan los headers si es que hay en la configuracion
         * para luego utilizarlos al construir la redireccion
         */
        if (count($this->headers) > 0) {
            if (!Settings::getInstance()->exists('headersInRedirect') || !Settings::getInstance()->get('headersInRedirect')) {
                Settings::getInstance()->set('headersInRedirect', $headers);
            }
        }

        header("Location: $this->path");
    }
}