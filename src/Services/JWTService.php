<?php

namespace InfamousQ\LManager\Services;

use \Firebase\JWT\JWT;

class JWTService implements TokenServiceInterface {

	/** @var UserServiceInterface $user_service */
	protected $user_service;

	protected $jwt_key = '';

	public function __construct(array $jwt_config, UserServiceInterface $user_service) {
		$this->jwt_key = $jwt_config['key'];
		$this->user_service = $user_service;
	}

	public function generateUserToken($user_id) {
		$user_data = $this->user_service->getUserById($user_id);
		$jwt_user_data = [
			'id' => $user_data->id,
			'name' => $user_data->name,
		];

		$jwt_token_data = [
			'iss' => 'dev.lmanager.test',
			'aud' => 'dev.lmanager.test',
			'iat' => time(),
			'exp' => time() + 60 * 60,
			'data' => $jwt_user_data,
		];
		return JWT::encode($jwt_token_data, $this->jwt_key);
	}
}