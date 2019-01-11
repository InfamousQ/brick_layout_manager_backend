<?php

namespace InfamousQ\LManager\Actions;

use \Slim\Http\StatusCode;

class GetAPIUserAction {

	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service */
	protected $user_service;

	public function __construct(\Slim\Container $container) {
		$this->user_service = $container->get('user');
	}

	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
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
}