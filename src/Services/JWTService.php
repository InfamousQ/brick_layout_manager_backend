<?php

namespace InfamousQ\LManager\Services;


class JWTService implements JWTServiceInterface {

	public function __construct() {

	}

	public function generateToken($user_id) {
		return 'JWT_TOKEN';
	}
}