<?php
/**
 * foreverPHP - Framework MVT (Model - View - Template)
 *
 * Template:
 *
 * Esta clase trabaja en conjunto con la vista para renderear la vista con
 * el template.
 *
 * @package     foreverPHP
 * @subpackage  cache
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @copyright   Copyright (c) 2014, Emarva.
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @link        http://www.emarva.com/foreverphp
 * @since       Version 0.1.0
 */

/*
 given a URL, try finding that page in the cache
if the page is in the cache:
    return the cached page
else:
    generate the page
    save the generated page in the cache (for next time)
    return the generated page
 */

/*
OJO: no va por url

¿Como se almacenan los templates?

    inicio.html.cache -> fonde inicio es el nombre del template

    Uso:
        Cache::set($tamplate . '.html', $render);

¿Como se almacenan los resultados de las consultas?

    mi_resultado_consulta.cache -> mi_resultado_consulta lo define el usuario
    o el ORM (tentacles)

    Uso:
        Cache::set('mi_resultado_consulta', $data);

        Nota: Los resultados se deberian guardar en formato JSon para una mejor
              comprension.

Cache::get: antes de devolver el objeto del cache debe verificar si ha expirado.

Cache::set: antes de guardar el objeto en el cache debe verificar que no se
            exceda el limite maximo de entidades.
*/

class Cache {
    private static $_cache_object = null;

    private static function _load_cache() {
        if (self::$_cache_object == null) {
           self::$_config_cache = Config::get('cache');


        }
    }

    public static function exists() {
        self::_load_cache();
    }

    public static function get() {
        self::_load_cache();
    }
    public static function set() {
        self::_load_cache();
    }

    public static function delete() {
        self::_load_cache();
    }
}
