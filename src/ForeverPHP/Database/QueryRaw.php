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

	private $dbSetting = 'default';

	private $database = false;

	private $dbInstance = null;

	private $hasError = false;

	private $error = '';

	private $parameters = array();

	private $query = null;

	private $queryType = 'select';

	private $queryReturn = 'num';

	private $autocommit = false;

	/**
	 * Contiene la instancia singleton de QueryRaw.
	 *
	 * @var \ForeverPHP\Database\QueryRaw
	 */
	private static $instance;

	public function __construct() {}

	/**
	 * Obtiene o crea la instancia singleton de QueryRaw.
	 *
	 * @return \ForeverPHP\Database\QueryRaw
	 */
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function using($dbSetting) {
		$this->dbSetting = $dbSetting;
		$this->database = false;
	}

	public function selectDatabase($database) {
		$this->database = $database;
	}

	public function autocommit($value = false) {
		$this->autocommit = $value;
	}

	public function query($query, $return = QR_FETCH_NUM) {
		$this->query = $query;

		// Debe detectar que tipo de consulta se va a ejecutar
		$queryInLCase = strtolower($query);

		if (strpos($queryInLCase, 'insert') !== false) {
			$this->queryType = 'insert';
		} elseif (strpos($queryInLCase, 'select') !== false) {
			$this->queryType = 'select';
		} elseif (strpos($queryInLCase, 'update') !== false) {
			$this->queryType = 'update';
		} elseif (strpos($queryInLCase, 'delete') !== false) {
			$this->queryType = 'delete';
		} else {
			$this->queryType = 'other';
		}

		unset($queryInLCase);

		if ($return == QR_FETCH_ASSOC) {
			$this->queryReturn = 'assoc';
		} elseif ($return == QR_FETCH_BOTH) {
			$this->queryReturn = 'both';
		} else {
			$this->queryReturn = 'num';
		}
	}

	public function addParameter($type, $value) {
		$count = count($this->parameters);

		// Cambia el parametro por el correcto
		if ($type == QR_PARAM_INTEGER) {
			$type = 'i';
		} elseif ($type == QR_PARAM_DOUBLE) {
			$type = 'd';
		} elseif ($type == QR_PARAM_STRING) {
			$type = 's';
		} elseif ($type == QR_PARAM_BLOB) {
			$type = 'b';
		}

		$this->parameters[$count] = array('type' => $type, 'value' => $value);
	}

	public function execute($returnType = QR_RETURN_ARRAY) {
		$this->dbInstance = null;
		$this->hasError = false;
		$this->error = '';
		$return = false;

		// Obtengo la configuracion de la base de datos a utilizar
		$selectDb = Settings::getInstance()->get('dbs');
		$selectDb[$this->dbSetting];
		$dbEngine = $selectDb[$this->dbSetting]['engine'];

		if ($dbEngine == 'mariadb') {
			$this->dbInstance = new namespace\Engines\MariaDB($this->dbSetting);
		} elseif ($dbEngine == 'mssql') {
			$this->dbInstance = new namespace\Engines\MSSQL($this->dbSetting);
		} elseif ($dbEngine == 'postgresql') {
			$this->dbInstance = new namespace\Engines\PostgreSQL($this->dbSetting);
		} elseif ($dbEngine == 'sqlsrv') {
			$this->dbInstance = new namespace\Engines\SQLSRV($this->dbSetting);
		} else {
			$this->error = 'Database engine not found.';
		}

		if ($this->dbInstance != null) {
			if ($this->database != false) {
				$this->dbInstance->selectDatabase($this->database);
			}

			// Me conecto al motor de datos
			if ($this->dbInstance->connect()) {
				$this->dbInstance->query($this->query, $this->queryType, $this->queryReturn);
				$this->dbInstance->setParameters($this->parameters);

				if ($result = $this->dbInstance->execute()) {
					if ($returnType == QR_RETURN_ARRAY) {
						$return = $result;
					} elseif ($returnType == QR_RETURN_JSON) {
						$return = json_encode($result, JSON_FORCE_OBJECT);
					}
				}

				// Me desconecto
				$this->dbInstance->disconnect();
			}

			// Recupera el ultimo error ocurrido en el motor de datos
			$this->error = $this->dbInstance->getError();

			if (!empty($this->error)) {
				$this->hasError = true;
				$return = false;
			}
		}

		// Se limpian las variables
		$this->dbInstance = null;
		$this->parameters = array();
		$this->query = '';
		$this->queryType = 'select';
		$this->queryReturn = 'num';

		return $return;
	}

	public function commit() {

	}

	public function hasError() {
		return $this->hasError;
	}

	public function getError() {
		return $this->error;
	}
}
