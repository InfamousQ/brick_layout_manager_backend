<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;
use Slim\Http\Response;

class GetUserTokenAction {
	/** @var \League\Plates\Engine $view */
	protected $view;

	public function __construct(\Slim\Container $container) {
		$this->view = $container->get('view');
	}

	public function __invoke(Request $request, Response $response) {
		return $this->view->render('user::token');
	}

}