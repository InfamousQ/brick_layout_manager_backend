<?php

namespace InfamousQ\LManager\Services;

interface DatabaseServiceInterface {

	public function __construct(array $config);

	/**
	 * @return \Pb\PDO\Database
	 */
	public function getPDO();
}