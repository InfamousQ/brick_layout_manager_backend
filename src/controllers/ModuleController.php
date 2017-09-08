<?php
namespace BLMRA\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as Container;

class ModuleController {
	protected $container;

	public function __construct (Container $container) {
		$this->container = $container;
	}

	public function view_single (Request $request, $response, $args) {
		$id = (int) $args['id'];
		$this->container->logger->info('Fetching module #'.$id);

		$module = array(
				'id' => $id,
			);
		$response->getBody()->write(json_encode($module));
		return $response;
	}

	public function edit_single (Request $request, $response) {
		$data = $request->getParsedBody();
		$module = array(
				'id' => (int) filter_var($data['id'], FILTER_SANITIE_STRING),
			);
		$response->getBody()->write(json_encode($module));
		return $response;
	}
}
