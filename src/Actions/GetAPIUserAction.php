<?php

namespace InfamousQ\LManager\Actions;

use \Slim\Http\StatusCode;

class GetAPIUserAction {

	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response) {
		$decoded_token = $request->getAttribute('token', null);
		$user_data = null;
		if (is_array($decoded_token)) {
			$user_data = (array_key_exists('data', $decoded_token)) ? $decoded_token['data'] : null;
		}
		if (null === $decoded_token || null === $user_data) {
			return $response->withJson(['error' => ['message' => 'Invalid token']], StatusCode::HTTP_BAD_REQUEST);
		}

		return $response->withJson($user_data);
	}
}