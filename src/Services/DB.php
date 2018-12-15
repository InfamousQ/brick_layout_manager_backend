<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 15.12.2018
 * Time: 21:08
 */

namespace InfamousQ\LManager\Services;

use Slim\PDO\Database as Database;

class DB {
	/** @var Database Current PDO instance */
	protected static $pdo_instance;

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
		/** TODO: Move to settings */
		$dsn = 'pgsql:host=bl_db;port=5432;dbname=lmanager_dev;';
		$user = 'bl_admin';
		$pass = 'test';
		self::$pdo_instance =  new Database($dsn, $user, $pass);
		return self::$pdo_instance;
	}
}