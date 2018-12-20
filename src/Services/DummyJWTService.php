<?php

namespace InfamousQ\LManager\Services;


class DummyJWTService implements JWTServiceInterface {

	public function __construct() {

	}

	public function generateToken($user_id) {
		return 'JWT_DUMMY_TOKEN';
	}
}