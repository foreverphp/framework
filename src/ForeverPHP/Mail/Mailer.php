<?php namespace ForeverPHP\Mail;

use ForeverPHP\Core\ClassLoader;
use ForeverPHP\Core\Settings;
use ForeverPHP\Core\Setup;
use PHPMailer;

/**
 * Permite enviar correos ya sea en formato texto como HTML.
 *
 * @since       Version 0.1.0
 */
class Mailer
{
    /**
     * Contiene la instancia singleton de Mailer.
     *
     * @var \ForeverPHP\Mail\Mailer
     */
    private static $instance;

    public function __construct()
    {
        //
    }

    /**
     * Obtiene o crea la instancia singleton de App.
     *
     * @return \ForeverPHP\Core\App
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function send($to, $subject, $message, $from, $attachmentPath = null, $attachmentName = null)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->from = $from;
        $this->mail = new PHPMailer();

        $settings = Settings::getInstance()->get('mail');

        $this->mail->isSMTP();
        $this->mail->Host = $settings['server'];
        $this->mail->Port = $settings['port'];

        if ($settings['smtpAuth']) {
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $settings['username'];
            $this->mail->Password = $settings['password'];

            if ($settings['smtpSecure'] != 'none') {
                $this->mail->SMTPSecure = $settings['smtpSecure'];
            }
        }

        $this->mail->From = $this->from;
        $this->mail->FromName = $this->from;
        //$mail->addAddress('joe@example.net', 'Joe User');
        $this->mail->addAddress($this->to);
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        $this->mail->WordWrap = 50;

        // Verifico si lleva adjunto
        if ($attachmentPath != null) {
            if ($attachmentName == null) {
                $this->mail->addAttachment($attachmentPath);
            } else {
                $this->mail->addAttachment($attachmentPath, $attachmentName);
            }
        }

        $this->mail->isHTML(true);

        $this->mail->Subject = $this->subject;
        $this->mail->Body    = $this->message;
        $this->mail->AltBody = $this->message;

        if (!$this->mail->send()) {
            $this->error = $this->mail->ErrorInfo;
            return false;
        }

        return true;
    }

    public function error()
    {
        return $this->error;
    }
}
