<?php

namespace ForeverPHP\Http;

use ForeverPHP\Session\SessionManager;

/**
 * Devuelve una respuesta de tipo de redireccion.
 *
 * @since 0.3.0
 */
class RedirectResponse implements ResponseInterface
{
    /**
     * Ruta a la cual redireccionar.
     *
     * @var string
     */
    private string $path;

    /**
     * Codigo de estado.
     *
     * @var integer
     */
    private int $status;

    /**
     * Encabezados a incluir en la redireccion.
     *
     * @var array
     */
    private array $headers;

    /**
     * Almacena la instancia unica del administrador de sesiones.
     *
     * @var \ForeverPHP\Session\SessionManager
     */
    private SessionManager $session;

    public function __construct(string $path, int $status = 302, array $headers = [])
    {
        $this->path = $path;
        $this->status = $status;
        $this->headers = $headers;

        // Obtiene la instancia del administrador de sesiones
        $this->session = SessionManager::getInstance();
    }

    /**
     * Añade datos que estarán disponibles después de la redirección.
     */
    public function with($key, $value = null): self
    {
        if ($key instanceof \ForeverPHP\View\Context) {
            return $this->with($key->all());
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->session->set($k, $v, 'redirect');
            }
        } else {
            $this->session->set($key, $value, 'redirect');
        }

        return $this;
    }

    /**
     * Ejecuta la redirección HTTP.
     */
    public function make()
    {
        // Guarda headers y path en sesión para uso posterior
        if (!empty($this->headers)) {
            if (!$this->session->existsAll(['redirectPath', 'headersInRedirect'], 'redirect')) {
                $this->session->set('redirectPath', $this->path, 'redirect');
                $this->session->set('headersInRedirect', $this->headers, 'redirect');
            }
        }

        // Envía headers personalizados antes de redirección
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Redirección HTTP con código especificado
        header("Location: {$this->path}", true, $this->status);
        exit();
    }
}
