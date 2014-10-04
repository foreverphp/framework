<?php
/**
 * foreverPHP - Framework MVT (Model - View - Template)
 *
 * RESTful:
 *
 * Base para el tratamiento de APIs RESTful.
 *
 * @package     foreverPHP
 * @subpackage  core
 * @author      Daniel Nuñez S. <dnunez@emarva.com>
 * @copyright   Copyright (c) 2014, Emarva.
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @link        http://www.emarva.com/foreverphp
 * @since       Version 0.1.0
 */

class RESTful {
	private $_state_code = 200;
	private $_states = array(
    	200 => 'OK',
       	201 => 'Created',
       	202 => 'Accepted',
       	204 => 'No Content',
       	301 => 'Moved Permanently',
       	302 => 'Found',
       	303 => 'See Other',
       	304 => 'Not Modified',
       	400 => 'Bad Request',
       	401 => 'Unauthorized',
       	403 => 'Forbidden',
       	404 => 'Not Found',
       	405 => 'Method Not Allowed',
       	500 => 'Internal Server Error');
	public $type = 'application/json';
	public $request_data = array();

	public function __construct() {
		$this->_analyze_request();
	}

	public function show_response() {

	}

	private function set_header() {

	}

	private function clean_request() {

	}

	private function _analyze_request() {

	}

	private function _get_state_code() {


		return;
	}
}
