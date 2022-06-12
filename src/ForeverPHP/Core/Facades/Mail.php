<?php namespace ForeverPHP\Core\Facades;

/**
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
