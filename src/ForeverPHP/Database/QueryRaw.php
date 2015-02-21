<?php namespace ForeverPHP\Database;

use ForeverPHP\Core\Settings;
use ForeverPHP\Core\Setup;

/**
 * Permite la ejecucion de consultas en bruto a la base de datos.
 *
 * @since       Version 0.1.0
 */

/*
 * Tipos de resultado a devolver.
 */
//Setup::toDefine('QR_FETCH_ASSOC', 0x11);
//Setup::toDefine('QR_FETCH_BOTH', 0x12);
//Setup::toDefine('QR_FETCH_NUM', 0x13);

/*
 * Tipos de retorno de los resultados.
 */
//Setup::toDefine('QR_RETURN_ARRAY', 0x21);
//Setup::toDefine('QR_RETURN_JSON', 0x22);

class QueryRaw {
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

	public function query($query, $fetch = 'array') {
		$this->query = $query;

		// Debe detectar que tipo de consulta se va a ejecutar
		$queryInLCase = lower($query);

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

		if (lower($fetch) == 'assoc') {
			$this->queryReturn = 'assoc';
		} elseif (lower($fetch) == 'both') {
			$this->queryReturn = 'both';
		} else {
			$this->queryReturn = 'num';
		}

		return $this;
	}

	public function addParameter($type, $value) {
		$count = count($this->parameters);

		$this->parameters[$count] = array('type' => $type, 'value' => $value);
	}

	public function execute($returnType = 'array') {
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
					if (lower($returnType) == 'json') {
						$return = json_encode($result, JSON_FORCE_OBJECT);
					} else {
						// array
						$return = $result;
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
