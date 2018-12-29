<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;
use Slim\Http\Response;

class GetUserLogoutAction {
	/** @var \InfamousQ\LManager\Services\AuthenticationServiceInterface $auth */
	protected $auth;

	public function __construct(\Slim\Container $container) {
		$this->auth = $container->get('auth');
	}

	public function __invoke(Request $request, Response $response) {
		$this->auth->disconnectAllAdapters();
		return $response->withRedirect('/');
	}
}