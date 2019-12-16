<?php

namespace InfamousQ\LManager\Actions;

use InfamousQ\LManager\Models\User;
use Slim\Http\Request;

trait readsUserDataFromToken {

	/** @var User User data from JWT token */
	public $token_user_data = null;

	public function getUserDataFromToken(Request $request, $trigger_exception_if_token_missing = true) {
		$decoded_token = $request->getAttribute('token', null);
		if (is_array($decoded_token) && array_key_exists('data', $decoded_token)) {
			$this->token_user_data = $decoded_token['data'] ?: new User();
		} else {
			$this->token_user_data = new User();
		}
		if ($trigger_exception_if_token_missing  && (null === $decoded_token || empty($this->token_user_data))) {
			throw new \InvalidArgumentException('No user data found from token');
		}
	}
}