<?php


namespace InfamousQ\LManager\Actions;

use InfamousQ\LManager\Models\Layout;
use InfamousQ\LManager\Models\LayoutModule;
use InfamousQ\LManager\Models\Module;
use InfamousQ\LManager\Models\Plate;
use \Slim\Http\Response;
use \Slim\Http\Request;
use \Slim\Http\StatusCode;

class GetSVGAction {

	/** @var \InfamousQ\LManager\Services\ModuleService $module_service */
	protected $module_service;

	/** @var \League\Plates\Engine $view */
	protected $view;

	public function __construct(\Slim\Container $container) {
		$this->module_service = $container->get('module');
		$this->view = $container->get('view');
	}

	public function generateLayoutSVG(Request $request, Response $response, array $args = []) {

		if (!array_key_exists('id', $args)) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		$target_layout_id = (int) $args['id'];
		/** @var Layout $target_layout */
		$target_layout = $this->module_service->getLayoutById($target_layout_id);
		if ($target_layout === null) {
			return $response->withJson(['error' => ['message' => 'Layout not found']], StatusCode::HTTP_NOT_FOUND);
		}

		// Prepare render data. Separate module data and module usage data from each other
		$module_definitions = [];
		$module_usages = [];
		/** @var LayoutModule $module_in_layout */
		foreach ($target_layout->modules as $module_in_layout) {
			// Find module information
			if (!array_key_exists($module_in_layout->module->id, $module_definitions)) {
				$temp_plate_data = [];
				/** @var Plate $plate */
				foreach ($module_in_layout->module->plates as $plate) {
					$temp_plate_data[] = [
						'x' => $plate->x,
						'y' => $plate->y,
						'w' => $plate->w,
						'h' => $plate->h,
						'fill' => $plate->color->hex,
					];
				}
				$module_definitions[$module_in_layout->module->id] = [
					'w' => $module_in_layout->module->w,
					'h' => $module_in_layout->module->h,
					'plates' => $temp_plate_data,
				];
			}

			// Find module usage (position and id of module) data
			$module_usages[] = [
				'x' => $module_in_layout->x,
				'y' => $module_in_layout->y,
				'module_id' => $module_in_layout->module->id,
			];
		}

		return $response
			->withHeader('Content-Type', 'image/svg+xml')
			->write($this->view->render('svg::layout', ['layout' => $target_layout, 'module_definitions' => $module_definitions, 'module_usages' => $module_usages]));
	}

	public function generateModuleSVG(Request $request, Response $response, array $args = []) {

		if (!array_key_exists('id', $args)) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_NOT_FOUND);
		}

		$target_module_id = (int) $args['id'];
		/** @var MOdule $target_module */
		$target_module = $this->module_service->getModuleById($target_module_id);
		if ($target_module === null) {
			return $response->withJson(['error' => ['message' => 'Module not found']], StatusCode::HTTP_NOT_FOUND);
		}

		// Prepare render data. Separate module data and module usage data from each other
		$plate_rects = [];
		/** @var Plate $plate */
		foreach ($target_module->plates as $plate) {
			$plate_rects[] = [
				'x' => $plate->x,
				'y' => $plate->y,
				'w' => $plate->w,
				'h' => $plate->h,
				'fill' => $plate->color->hex,
			];
		}

		return $response
			->withHeader('Content-Type', 'image/svg+xml')
			->write($this->view->render('svg::module', ['module' => $target_module, 'plate_rects' => $plate_rects]));
	}
}