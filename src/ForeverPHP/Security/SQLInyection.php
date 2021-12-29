<?php namespace ForeverPHP\Security;

/**
 * foreverPHP - Framework MVT (Model - View - Template)
 *
 * SQLInyection:
 *
 * Controla la posible insercion de codigo de inyeccion SQL.
 *
 * @package     foreverPHP
 * @subpackage  security
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @copyright   Copyright (c) 2014, Emarva.
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @link        http://www.emarva.com/foreverphp
 * @since       Version 0.1.0
 */

class SQLInyection
{
    private $keywords = array(
        'SELECT', 'select',
        'COPY', 'copy',
        'DELETE', 'delete',
        'DROP', 'drop',
        'DUMP', 'dump',
        ' OR ', ' or ',
        '%',
        'LIKE', 'like',
        '--',
        '^',
        '[',
        ']',
        '\\',
        '!',
        '¡',
        '?',
        '=',
        '&',
    );

    public static function analize($query)
    {
        //
    }
}
