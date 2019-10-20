<?php

namespace InfamousQ\LManager\Services;

interface MapperServiceInterface{

	/**
	 * Generate Mapper instance for given $entity_name
	 * @param string $entity_name Entity name
	 * @return \Spot\MapperInterface
	 */
	public static function getMapper($entity_name) : \Spot\MapperInterface;

	public static function closeConnectionToDB();
}