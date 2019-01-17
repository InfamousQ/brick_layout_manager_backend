<?php

namespace InfamousQ\LManager\Services;

interface DatabaseServiceInterface {

	public function __construct(array $config);

	/**
	 * @return \PDO
	 */
	public function getPDO();
}