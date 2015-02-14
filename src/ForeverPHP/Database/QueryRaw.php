<?php namespace ForeverPHP\Database;

use ForeverPHP\Core\Settings;

/**
 * Permite la ejecucion de consultas en bruto a la base de datos.
 *
 * @since       Version 0.1.0
 */
class QueryRaw {
	/*
	 * Constantes de tipo de consulta.
	 */
	const QR_QUERY_INSERT = 0x01;
	const QR_QUERY_SELECT = 0x02;
	const QR_QUERY_UPDATE = 0x03;
	const QR_QUERY_DELETE = 0x04;
	const QR_QUERY_OTHER = 0x05;

	/*
	 * Tipos de resultado a devolver.
	 */
	const QR_FETCH_ASSOC = 0x11;
	const QR_FETCH_BOTH = 0x12;
	const QR_FETCH_NUM = 0x13;

	/*
	 * Tipos de retorno de los resultados.
	 */
	const QR_RETURN_ARRAY = 0x21;
	const QR_RETURN_JSON = 0x22;

	/*
	 * Tipos de parametros.
	 *
	 * Nota: Estas constantes estan obsoletas, solo estan para
	 * soporte a versiones anteriores.
	 */
	const QR_PARAM_INTEGER = 0x31;
	const QR_PARAM_DOUBLE = 0x32;
	const QR_PARAM_STRING = 0x33;
	const QR_PARAM_BLOB = 0x34;

	private static $dbSetting = 'default';

	private static $database = false;

	private static $hasError = false;

	private static $error = '';

	private static $parameters = array();

	private static $query = null;

	private static $queryType = 'select';

	private static $queryReturn = 'num';

	private static $autocommit = false;

	public static function using($dbSetting) {
		static::$dbSetting = $dbSetting;
		static::$database = false;
	}

	public static function selectDatabase($database) {
		static::$database = $database;
	}

	public static function autocommit($value = false) {
		static::$autocommit = $value;
	}

	public static function query($query, $return = self::QR_FETCH_NUM) {
		static::$query = $query;

		// Debe detectar que tipo de consulta se va a ejecutar
		$queryInLCase = strtolower($query);

		if (strpos($queryInLCase, 'insert') !== false) {
			static::$queryType = 'insert';
		} elseif (strpos($queryInLCase, 'select') !== false) {
			static::$queryType = 'select';
		} elseif (strpos($queryInLCase, 'update') !== false) {
			static::$queryType = 'update';
		} elseif (strpos($queryInLCase, 'delete') !== false) {
			static::$queryType = 'delete';
		} else {
			static::$queryType = 'other';
		}

		unset($queryInLCase);

		if ($return == static::QR_FETCH_ASSOC) {
			static::$queryReturn = 'assoc';
		} elseif ($return == static::QR_FETCH_BOTH) {
			static::$queryReturn = 'both';
		} else {
			static::$queryReturn = 'num';
		}
	}

	public static function addParameter($type, $value) {
		$count = count(static::$parameters);

		// Cambia el parametro por el correcto
		if ($type == static::QR_PARAM_INTEGER) {
			$type = 'i';
		} elseif ($type == static::QR_PARAM_DOUBLE) {
			$type = 'd';
		} elseif ($type == static::QR_PARAM_STRING) {
			$type = 's';
		} elseif ($type == static::QR_PARAM_BLOB) {
			$type = 'b';
		}

		static::$parameters[$count] = array('type' => $type, 'value' => $value);
	}

	public static function execute($returnType = self::QR_RETURN_ARRAY) {
		$db = null;
		$return = false;
		static::$hasError = false;
		static::$error = '';

		// Obtengo la configuracion de la base de datos a utilizar
		$selectDb = Settings::getInstance()->get('dbs');
		$selectDb[static::$dbSetting];
		$dbEngine = $selectDb[static::$dbSetting]['engine'];

		if ($dbEngine == 'mariadb') {
			$db = namespace\Engines\MariaDB::getInstance(static::$dbSetting, static::$database);
		} elseif ($dbEngine == 'mssql') {
			$db = namespace\Engines\MSSQL::getInstance(static::$dbSetting, static::$database);
		} elseif ($dbEngine == 'postgresql') {
			$db = namespace\Engines\PostgreSQL::getInstance(static::$dbSetting, static::$database);
		} elseif ($dbEngine == 'sqlsrv') {
			$db = namespace\Engines\SQLSRV::getInstance(static::$dbSetting, static::$database);
		} else {
			static::$error = 'Database engine not found.';
		}

		if ($db != null) {
			// Me conecto al motor de datos
			if ($db->connect()) {
				$db->query(static::$query, static::$queryType, static::$queryReturn);
				$db->setParameters(static::$parameters);

				if ($result = $db->execute()) {
					if ($returnType == static::QR_RETURN_ARRAY) {
						$return = $result;
					} elseif ($returnType == static::QR_RETURN_JSON) {
						$return = json_encode($result, JSON_FORCE_OBJECT);
					}
				}

				// Me desconecto
				$db->disconnect();
			}

			// Recupera el ultimo error ocurrido en el motor de datos
			static::$error = $db->getError();

			if (!empty(static::$error)) {
				static::$hasError = true;
			}

			unset($db);
		}

		// Se limpian las variables
		static::$parameters = array();
		static::$query = '';
		static::$queryType = 'select';
		static::$queryReturn = 'num';

		return $return;
	}

	public static function commit() {

	}

	public static function hasError() {
		return static::$hasError;
	}

	public static function getError() {
		return static::$error;
	}
}
