<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 17.12.2018
 * Time: 23:36
 */

namespace InfamousQ\LManager\Actions;


use Slim\Http\Request;
use Slim\Http\Response;

class GetPingAction {

	public function __invoke(Request $request, Response $response) {
		return $response->withJson(['data' => ['message' => 'pong']]);
	}

}