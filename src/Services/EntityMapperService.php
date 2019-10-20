<?php

namespace InfamousQ\LManager\Services;

class EntityMapperService implements MapperServiceInterface {

	/** @var \Spot\Locator Spot mapper instance*/
	protected static $mapper_instance;

	public function __construct(array $config) {
		$spot_config = new \Spot\Config();
		$spot_config->addConnection('pgsql', [
			'dbname' => $config['dbname'],
			'user' => $config['user'],
			'password' => $config['password'],
			'host' => $config['host'],
			'driver' => 'pdo_pgsql']);
		self::$mapper_instance = new \Spot\Locator($spot_config);
	}

	public static function getMapper($entity_name): \Spot\MapperInterface {
		return self::$mapper_instance->mapper($entity_name);
	}

	public static function closeConnectionToDB() {
		self::$mapper_instance->config()->connection('pgsql')->close();
		return true;
	}
}