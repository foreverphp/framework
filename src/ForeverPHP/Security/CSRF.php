<?php namespace ForeverPHP\Security;

/**
 * Controla y evita los ataques de tipo Cross Site Request Forgery.
 *
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @since       Version 0.1.0
 *
 * IMPORTANTE: La clase CSRF, se debe usar siempre que se este manipulando datos
 *             del usuario o de la base de datos para evitar ataques.
 */
class CSRF
{
    public static function generateToken()
    {
        // Genero un token seguro
        $token = md5(uniqid(microtime(), true));

        $newToken = base64_encode($token);

        /**
         * Creo el token
         *
         * Si no hay una sesion activa el token se guarda en una cookie.
         */
        if (Session::exists('token')) {
            Session::set('token', $newToken);
        } else {
            // Creo una cookie que vence en un año
            Cookie::set('csrfToken', $newToken, time() + 86400 * 365);
        }

        return $new_token;
    }

    public static function validateToken()
    {
        $token = '';

        if (Session::exists('token')) {
            $token = base64_decode(Session::get('token'));
        } elseif (Cookie::exists('csrfToken')) {
            /**
             * No es necesario borrar el token en cookie ya que se
             * sobreescribira solo cada ves que se realice una llamada a un
             * formulario que utilice token CSRF.
             */
            $token = base64_decode(Cookie::get('csrfToken'));
        }

        if (!empty($token)) {
            $tokenRequest = base64_decode(Settings::getInstance()->get('csrfToken'));

            // Verifica que los tokens coincidan
            if ($tokenRequest === $token) {
                return true;
            }
        } else {
            return false;
        }
    }
}
