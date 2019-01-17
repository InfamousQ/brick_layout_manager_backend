<?php

namespace InfamousQ\LManager\Services;

class PDODatabaseService implements DatabaseServiceInterface {
	/** @var \PDO Current PDO instance */
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
	 * @return \PDO
	 */
	public static function getInstance() {
		if (null == self::$pdo_instance) {
			return self::initializePDO();
		}
		return self::$pdo_instance;
	}

	protected static function initializePDO() {
		self::$pdo_instance =  new \PDO(self::$pdo_dsn_string, self::$user, self::$password);
		return self::$pdo_instance;
	}
}