<?php

namespace InfamousQ\LManager\Services;

use \Pb\PDO\Database as Database;

class PDODatabaseService implements DatabaseServiceInterface {
	/** @var Database Current PDO instance */
	protected static $pdo_instance;

	protected static $pdo_dsn_string = '';
	protected static $user = '';
	protected static $password = '';

	public function __construct(array $config) {
		self::$pdo_dsn_string = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
		self::$user = $config['user'];
		self::$password = $config['password'];
	}

	public function getPDO() {
		return self::getInstance();
	}

	/**
	 * Get current PDO instance
	 * @return Database
	 */
	public static function getInstance() {
		if (null == self::$pdo_instance) {
			return self::initializePDO();
		}
		return self::$pdo_instance;
	}

	protected static function initializePDO() {
		self::$pdo_instance =  new Database(self::$pdo_dsn_string, self::$user, self::$password);
		return self::$pdo_instance;
	}
}