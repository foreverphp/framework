<?php

namespace ForeverPHP\Http;

/**
 * Contiene información y operaciones de un archivo enviado en el request.
 *
 * Proporciona métodos para acceder a nombre, extensión, MIME type, tamaño,
 * estado de error y mover el archivo a un directorio específico.
 *
 * @since Version 0.4.0
 */
class RequestFile
{
    /**
     * Nombre completo del archivo tal como se subió.
     *
     * @var string
     */
    private string $filename = '';

    /**
     * Nombre del archivo sin extensión.
     *
     * @var string
     */
    private string $name = '';

    /**
     * Extensión del archivo (sin el punto).
     *
     * @var string
     */
    private string $extension = '';

    /**
     * MIME type del archivo.
     *
     * @var string
     */
    private string $mimetype = '';

    /**
     * Ruta temporal donde PHP almacena el archivo subido.
     *
     * @var string
     */
    private string $realPath = '';

    /**
     * Código de error de la subida, según las constantes UPLOAD_ERR_* de PHP.
     *
     * @var int
     */
    private int $error = 0;

    /**
     * Tamaño del archivo en bytes.
     *
     * @var int
     */
    private int $size = 0;

    /**
     * Constructor.
     *
     * Inicializa la instancia con los datos del array $_FILES.
     *
     * @param array $fileInfo Array con información del archivo (ej. $_FILES['field'])
     */
    public function __construct(array $fileInfo)
    {
        $this->filename = is_array($fileInfo['name']) ? $fileInfo['name'][0] : $fileInfo['name'];

        $this->setNameAndExtension($this->filename);

        $this->mimetype = $fileInfo['type'] ?? '';
        $this->realPath = $fileInfo['tmp_name'] ?? '';
        $this->error = $fileInfo['error'] ?? UPLOAD_ERR_OK;
        $this->size = $fileInfo['size'] ?? 0;
    }

    /**
     * Separa el nombre y la extensión del archivo, incluso si tiene múltiples puntos.
     *
     * @param string $filename
     * @return void
     */
    private function setNameAndExtension(string $filename): void
    {
        $pathInfo = pathinfo($filename);
        $this->name = $pathInfo['filename'] ?? '';
        $this->extension = $pathInfo['extension'] ?? '';
    }

    /**
     * Obtiene el nombre completo del archivo.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Obtiene el nombre del archivo sin extensión.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Obtiene la extensión del archivo.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Obtiene el MIME type del archivo.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimetype;
    }

    /**
     * Obtiene la ruta temporal donde PHP almacena el archivo.
     *
     * @return string
     */
    public function getRealPath(): string
    {
        return $this->realPath;
    }

    /**
     * Indica si el archivo tiene algún error de subida.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

     /**
     * Obtiene el código de error de la subida.
     *
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Obtiene el código de error de la subida.
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Mueve el archivo a la ruta especificada.
     *
     * Crea el directorio si no existe y asegura que la ruta termine con un slash.
     *
     * @param string $path Directorio destino
     * @param string|null $filename Nombre opcional del archivo
     * @return bool Retorna true si se movió correctamente, false en caso contrario
     */
    public function move(string $path, ?string $filename = null): bool
    {
        $newFilename = $filename ?? $this->filename;

        // Asegura que la ruta termina con '/'
        $path = rtrim($path, '/\\') . '/';

        // Crea el directorio si no existe
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return move_uploaded_file($this->realPath, "$path$newFilename");
    }
}
