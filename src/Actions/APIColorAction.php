<?php


namespace InfamousQ\LManager\Actions;

use InfamousQ\LManager\Models\APIColorMapper;
use \Slim\Http\Response;
use \Slim\Http\Request;
use Slim\Http\StatusCode;

class APIColorAction {

	/** @var \InfamousQ\LManager\Services\ModuleService $module_service */
	protected $module_service;

	public function __construct(\Slim\Container $container) {
		$this->module_service = $container->get('module');
	}

	public function fetchList(Request $request, Response $response) {
		$colors = $this->module_service->getColors()->execute();
		$json_data = [];
		/** @var Color $color */
		foreach ($colors as $color) {
			$json_data[] = APIColorMapper::getJSON($color);
		}
		return $response->withJson($json_data, StatusCode::HTTP_OK);
	}
}