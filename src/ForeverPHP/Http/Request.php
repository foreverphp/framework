<?php

namespace ForeverPHP\Http;

use ForeverPHP\Core\Settings;

/**
 * Contiene parámetros e información del request HTTP.
 *
 * @since Version 0.4.0
 */
class Request
{
    /** @var bool Indica si el request ya fue registrado */
    private bool $registered = false;

    /** @var array Archivos subidos */
    private array $files = [];

    /** @var string Método HTTP del request */
    private string $method = 'get';

    /** @var array Parámetros del request */
    private array $params = [];

    /** @var \ForeverPHP\Http\Request Instancia singleton */
    private static ?Request $instance = null;

    public function __construct() {}

    /**
     * Obtiene o crea la instancia singleton de Request.
     *
     * @return \ForeverPHP\Http\Request
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Carga los parámetros y archivos del request.
     *
     * @return void
     */
    private function loadRequest(): void
    {
        if ($this->registered) {
            return;
        }

        // Determinar el tipo de contenido
        $contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';

        // Determinar el método
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestParams = [];

        $this->method = strtolower($requestMethod);

        switch ($requestMethod) {
            case 'GET':
                $requestParams = $_GET;
                break;

            case 'POST':
                $requestParams = str_contains($contentType, 'application/json')
                    ? (json_decode(file_get_contents('php://input'), true) ?: [])
                    : $_POST;
                break;

            case 'PUT':
            case 'DELETE':
                /**
                 * PHP no tiene un método propiamente dicho para leer una petición PUT o DELETE,
                 * por lo que se usa un "truco".
                 * Leer el stream de entrada file_get_contents("php://input") que transfiere un
                 * fichero a una cadena.
                 * Con ello obtenemos una cadena de pares clave valor de variables
                 * (variable1=dato1&variable2=data2...) que evidentemente tendremos que
                 * transformarla a un array asociativo.
                 */
                $input = file_get_contents('php://input');

                if (str_contains($contentType, 'application/json')) {
                    $requestParams = json_decode($input, true) ?: [];
                } else {
                    parse_str($input, $requestParams);
                }
                break;
        }

        // Archivos subidos
        foreach ($_FILES as $name => $value) {
            $this->files[$name] = new RequestFile($value);
        }

        // Asignación de parámetros y keys especiales (como csrfToken)
        $specialKeys = ['csrfToken'];
        foreach ($requestParams as $name => $value) {
            if (in_array($name, $specialKeys)) {
                Settings::getInstance()->set($name, $value);
            } else {
                $this->params[$name] = $value;
            }
        }

        $this->registered = true;
    }

    /**
     * Registra el request manualmente.
     *
     * @param array|null $params Parámetros a registrar
     * @return void
     */
    public function register(?array $params = null): void
    {
        if ($this->registered) {
            return;
        }

        if ($params !== null && count($params) > 0) {
            $this->params = $params;
        }

        // La separe para que siempre procese el Request para poder procesar parametros, $_GET, $_POST, etc.
        $this->loadRequest();

        $this->registered = true;
    }

    /**
     * Obtiene la instancia de Host.
     *
     * @return \ForeverPHP\Http\Host
     */
    public function host(): Host
    {
        return Host::getInstance();
    }

    /**
     * Retorna el método HTTP del request.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Valida si el request es del método indicado.
     *
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return strtolower($method) === $this->method;
    }

    /**
     * Verifica si existe un parámetro en el request.
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->params[$name]);
    }

    /**
     * Obtiene el valor de un parámetro del request.
     *
     * @param string $name
     * @param string|int|array|null $default
     * @return mixed
     */
    public function get(string $name, string|int|array|null $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key);
        return is_string($value) ? $value : $default;
    }

    /**
     * @param string $key
     * @param int $default
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param string $key
     * @param float $default
     * @return float
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key);
        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        return is_string($value) || is_numeric($value) ? (bool) $value : $default;
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $default
     * @return array<array-key, mixed>
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key);
        return is_array($value) ? $value : $default;
    }

    /**
     * Retorna todos los parámetros del request.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->params;
    }

    /**
     * Verifica si existe un archivo subido.
     *
     * @param string $name
     * @return bool
     */
    public function hasFile(string $name): bool
    {
        return isset($this->files[$name]);
    }

    /**
     * Obtiene un archivo subido.
     *
     * @param string $name
     * @return RequestFile|false
     */
    public function file(string $name): RequestFile|false
    {
        return $this->files[$name] ?? false;
    }

    /**
     * Retorna todos los archivos subidos.
     *
     * @return array
     */
    public function allFiles(): array
    {
        return $this->files;
    }

    // Métodos pendientes de implementación
    public function path() {}

    public function url() {}

    public function segment(int $number) {}

    public function is(string $path) {}

    public function header(string $name) {}

    public function server(string $var) {}

    public function secure() {}

    public function ajax() {}

    public function isJson() {}

    public function wantsJson() {}

    public function format(string $format) {}
}
