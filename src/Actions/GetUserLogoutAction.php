<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;
use Slim\Http\Response;

class GetUserLogoutAction extends AbstractAction {

	public function __invoke(Request $request, Response $response) {
		$this->container->auth->disconnectAllAdapters();
		return $response->withRedirect('/');
	}
}