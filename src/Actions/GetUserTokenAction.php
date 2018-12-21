<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;
use Slim\Http\Response;

class GetUserTokenAction {

	public function __invoke(Request $request, Response $response) {
		return $response->withJson(['token' => 'not_implemented']);
	}

}