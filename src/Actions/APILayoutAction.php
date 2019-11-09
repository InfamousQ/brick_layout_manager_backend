<?php


namespace InfamousQ\LManager\Actions;

use InfamousQ\LManager\Models\APILayoutMapper;
use InfamousQ\LManager\Models\LayoutModule;
use \Slim\Http\Response;
use \Slim\Http\Request;
use Slim\Http\StatusCode;

class APILayoutAction {

	use readsUserDataFromToken;

	/** @var \InfamousQ\LManager\Services\ModuleService $module_service */
	protected $module_service;
	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service */
	protected $user_service;

	public function __construct(\Slim\Container $container) {
		$this->module_service = $container->get('module');
		$this->user_service = $container->get('user');
	}

	public function fetchList(Request $request, Response $response) {

		try {
			$this->getUserDataFromToken($request, false);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null == $current_user) {
			$layouts = $this->module_service->getPublicLayouts();
		} else {
			$layouts = $this->module_service->getLayouts($current_user->id);
		}
		$result = [];
		foreach ($layouts as $layout) {
			$result[] = APILayoutMapper::getSummaryJSON($layout);
		}
		return $response->withJson($result);
	}

	public function insert(Request $request, Response $response) {

		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$json_fields = $request->getParsedBody();
		$allowed_layout_field_keys = ['name'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_layout_field_keys));
		$new_layout = $this->module_service->createLayout($allowed_json_fields['name'], $current_user->id);
		if (null === $new_layout) {
			// TODO: send error
			error_log('layout creation fail');
		}

		return $response->withJson(APILayoutMapper::getJSON($new_layout), StatusCode::HTTP_OK);
	}

	public function fetchSingle(Request $request, Response $response, array $args = array()) {

		if (!array_key_exists('id', $args)) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		// Fetch the target layout. Check it's public-value. Public layouts are returned to anyone with or without token but private require that right user is using API.
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			if (!$target_layout->public) {
				return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
			}
		}

		return $response->withJson(APILayoutMapper::getJSON($target_layout), StatusCode::HTTP_OK);
	}

	public function editSingle(Request $request, Response $response, array $args = array()) {

		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		if (!array_key_exists('id', $args)) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		// Fetch the target layout. Only authoring user can commence edits
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		if ($target_layout->user->id !== $current_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$json_fields = $request->getParsedBody();
		$allowed_layout_field_keys = ['name', 'w', 'h'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_layout_field_keys));
		foreach ($allowed_json_fields as $field => $value) {
			$target_layout->$field = $value;
		}
		if (!$this->module_service->saveLayout($target_layout)) {
			return $response->withJson(['error' => ['message' => 'Layout saving failed']], StatusCode::HTTP_BAD_REQUEST);
		}

		return $response->withJson(APILayoutMapper::getJSON($target_layout), StatusCode::HTTP_OK);
	}

	public function deleteSingle(Request $request, Response $response, array $args = array()) {

		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target layout. Delete it only if active user is the author of the layout.
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		if ($target_layout->user->id !== $current_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		if (!$this->module_service->deleteLayoutById($target_layout->id)) {
			return $response->withJson(['error' => ['message' => 'Deleting layout failed']], StatusCode::HTTP_BAD_REQUEST);
		}

		return $response->withJson('', StatusCode::HTTP_NO_CONTENT);
	}

	public function addModuleToLayout(Request $request, Response $response, array $args = array()) {

		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target layout. Only author can edit the layout.
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		if ($target_layout->user->id !== $current_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Read module data from request and create new LayoutModule based on them
		$json_fields = $request->getParsedBody();
		$allowed_layout_module_field_keys = ['id', 'x', 'y'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_layout_module_field_keys));
		$target_module = $this->module_service->getModuleById((int) $allowed_json_fields['id']);
		if (null === $target_module) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
		}
		$allowed_json_fields['layout_id'] = $target_layout->id;
		if (!$this->module_service->connectModuleToLayout($target_layout, $target_module, (int) $allowed_json_fields['x'], (int) $allowed_json_fields['y'])) {
			return $response->withJson(['error' => ['message' => 'Adding module to layout failed']], StatusCode::HTTP_BAD_REQUEST);
		}

		return $response->withJson(APILayoutMapper::getJSON($target_layout));
	}

	public function editModuleInLayout(Request $request, Response $response, array $args = array()) {
		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target layout. Only author can edit the layout.
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		if ($target_layout->user->id !== $current_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target module, ensure that the module is connected to the layout
		$target_module_id = (int) $args['module_id'];
		if ($target_module_id < 1) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_BAD_REQUEST);
		}
		$target_layout_module = null;
		/** @var LayoutModule $m */
		foreach ($target_layout->modules as $m) {
			if ($m->module->id === $target_module_id) {
				$target_layout_module = $m;
				break;
			}
		}
		if (null === $target_layout_module) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_BAD_REQUEST);
		}

		$json_fields = $request->getParsedBody();
		$allowed_layout_module_fields = ['x', 'y'];
		$allowed_json_fields = array_intersect_key($json_fields, array_flip($allowed_layout_module_fields));
		foreach ($allowed_json_fields as $field => $value) {
			$target_layout_module->$field = $value;
		}
		if (!$this->module_service->saveModuleInLayout($target_layout_module)) {
			return $response->withJson(['error' => ['message' => 'Editing module in layout failed']], StatusCode::HTTP_BAD_REQUEST);
		}
		return $response->withJson(APILayoutMapper::getJSON($target_layout));
	}

	public function deleteModuleInLayout(Request $request, Response $response, array $args = array()) {
		try {
			$this->getUserDataFromToken($request);
		} catch (\InvalidArgumentException $e) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		$current_user = $this->user_service->getUserById($this->token_user_data->id);
		if (null === $current_user) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target layout. Only author can edit the layout.
		$target_layout_id = (int) $args['id'];
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if (null === $target_layout) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		if ($target_layout->user->id !== $current_user->id) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_UNAUTHORIZED);
		}

		// Fetch target module, ensure that the module is connected to the layout
		$target_module_id = (int) $args['module_id'];
		if ($target_module_id < 1) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_BAD_REQUEST);
		}
		$target_layout_module = null;
		/** @var LayoutModule $m */
		foreach ($target_layout->modules as $m) {
			if ($m->module->id === $target_module_id) {
				$target_layout_module = $m;
				break;
			}
		}
		if (null === $target_layout_module) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_BAD_REQUEST);
		}

		if (!$this->module_service->deleteModuleInLayout($target_layout_module)) {
			return $response->withJson(['error' => ['message' => 'Editing module in layout failed']], StatusCode::HTTP_BAD_REQUEST);
		}
		// Currently Spot ORM entity's relations cannot be update, we just need to reload data again.
		$resulting_layout = $this->module_service->getLayoutById($target_layout->id);
		return $response->withJson(APILayoutMapper::getJSON($resulting_layout));
	}
}