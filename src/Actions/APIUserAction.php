<?php

namespace InfamousQ\LManager\Actions;

use \Slim\Http\Response;
use \Slim\Http\Request;
use \Slim\Http\StatusCode;

class APIUserAction {

	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service */
	protected $user_service;
	/** @var \InfamousQ\LManager\Services\AuthenticationServiceInterface $auth_service */
	protected $auth_service;

	public function __construct(\Slim\Container $container) {
		$this->auth_service = $container->get('auth');
		$this->user_service = $container->get('user');
	}

	public function fetch(Request $request, Response $response, array $args = array()) {
		$decoded_token = $request->getAttribute('token', null);
		$user_data = null;
		if (is_array($decoded_token)) {
			$user_data = (array_key_exists('user', $decoded_token)) ? $decoded_token['user'] : null;
		}
		if (null === $decoded_token || null === $user_data) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		if (array_key_exists('id', $args)) {
			$target_user_id = $args['id'];
		} else {
			$target_user_id = $user_data['id'];
		}

		// Find user that was provided by token's meta data.
		$target_user = $this->user_service->getUserById($target_user_id);
		return $response->withJson($target_user->getData(), StatusCode::HTTP_OK);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function update(Request $request, Response $response, array $args = array()) {
		$decoded_token = $request->getAttribute('token', null);
		$user_data = null;
		if (is_array($decoded_token)) {
			$user_data = (array_key_exists('user', $decoded_token)) ? $decoded_token['user'] : null;
		}
		if (null === $decoded_token || null === $user_data) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		if (!is_array($args) || !array_key_exists('id', $args)) {
			return $response->withJson(['error' => ['message' => 'Invalid user id']], StatusCode::HTTP_BAD_REQUEST);
		}
		$target_user_id = $args['id'];
		$target_user = $this->user_service->getUserById($target_user_id);
		if (null == $target_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid user id']], StatusCode::HTTP_BAD_REQUEST);
		}

		$json_fields = $request->getParsedBody();
		$allowed_user_fields_keys = ['name'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_user_fields_keys));
		foreach ($allowed_json_fields as $key => $value) {
			$target_user->$key = $value;
		}
		if ($this->user_service->saveUser($target_user)) {
			return $response->withJson($target_user->getData(), StatusCode::HTTP_OK);
		} else {
			return $response->withJson(['error' => ['message' => 'Error occured while saving user']], StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function providers(Request $request, Response $response) {
		$available_providers = $this->auth_service->getAvailableProviders();
		$json_data = [];
		foreach ($available_providers as $available_provider) {
			$json_data[] = [
				'name' => $available_provider['name'],
				'code' => $available_provider['code'],
				'icon' => $available_provider['icon'],
			];
		}
		return $response->withJson($json_data, StatusCode::HTTP_OK);
	}
}