<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;

trait readsUserDataFromToken {

	public $token_user_data = null;

	public function getUserDataFromToken(Request $request) {
		$decoded_token = $request->getAttribute('token', null);
		if (is_array($decoded_token)) {
			$this->token_user_data = $decoded_token['data'] ?: new \stdClass();
		}
		if (null === $decoded_token || empty($this->token_user_data)) {
			throw new \InvalidArgumentException('No user data found from token');
		}
	}
}