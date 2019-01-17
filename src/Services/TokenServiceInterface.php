<?php

namespace InfamousQ\LManager\Services;


interface TokenServiceInterface {

	public function __construct(array $jwt_config, UserServiceInterface $user_service);

	/**
	 * Generate new JWT token for given $user_id. Returns JWT token
	 * @param int $user_id User id
	 * @return string JWT token
	 */
	public function generateUserToken($user_id);
}