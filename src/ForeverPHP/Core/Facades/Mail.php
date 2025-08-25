<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool send(string $to, string $subject, string $message, string $from, string $attachmentPath = null, string $attachmentName = null)
 * @method static string error()
 * @see \ForeverPHP\Mail\Mailer
 */
class Mail extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Mail\Mailer::getInstance();
    }
}
