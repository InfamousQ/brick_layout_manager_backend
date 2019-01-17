<?php

namespace InfamousQ\LManager\Services;


class DummyJWTService implements TokenServiceInterface {

	public function __construct(array $jwt_config, UserServiceInterface $user_service) {
	}

	public function generateUserToken($user_id) {
		return 'DUMMY_JWT_TOKEN';
	}
}