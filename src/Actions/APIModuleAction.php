<?php

namespace InfamousQ\LManager\Actions;

use InfamousQ\LManager\Models\APIModuleMapper;
use \Slim\Http\Response;
use \Slim\Http\Request;
use \Slim\Http\StatusCode;

class APIModuleAction {
	/** @var \InfamousQ\LManager\Services\ModuleService $module_service */
	protected $module_service;
	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service */
	protected $user_service;

	public function __construct(\Slim\Container $container) {
		$this->module_service = $container->get('module');
		$this->user_service = $container->get('user');
	}

	public function fetchList(Request $request, Response $response) {
		$public_modules = $this->module_service->getPublicModules();
		$result = [];
		foreach ($public_modules as $public_module) {
			$result[] = APIModuleMapper::getSummaryJSON($public_module);
		}
		return $response->withJson($result, StatusCode::HTTP_OK);
	}

	public function insert(Request $request, Response $response, array $args = []) {
		$decoded_token = $request->getAttribute('token', null);
		$user_data = null;
		if (is_array($decoded_token)) {
			$user_data = (array_key_exists('user', $decoded_token)) ? $decoded_token['user'] : null;
		}
		if (null === $decoded_token || null === $user_data || empty($user_data['id'])) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($user_data['id']);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}
		$json_fields = $request->getParsedBody();
		$allowed_module_field_keys = ['name'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_module_field_keys));
		$new_module = $this->module_service->createModule($allowed_json_fields['name'], $current_user->id);
		if (null === $new_module) {
			// TODO: send error
			error_log('fail!');
		}
		return $response->withJson(APIModuleMapper::getJSON($new_module), StatusCode::HTTP_OK);

	}
}