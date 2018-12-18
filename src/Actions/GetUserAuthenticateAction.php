<?php

namespace InfamousQ\LManager\Actions;

use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Hybridauth;
use Slim\Http\Request;
use Slim\Http\Response;
use InfamousQ\LManager\Services\AuthenticationServiceInterface;
use InfamousQ\LManager\Services\UserService;

class GetUserAuthenticateAction extends AbstractAction {

	/** @var $auth Hybridauth authentication service*/
	protected $auth;

	public function __construct(AuthenticationServiceInterface $authenticationService) {
		$this->auth = $authenticationService;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response) {
		/** @var string $param_provider Code of the Hybridauth provider to be used */
		$param_provider = $request->getQueryParam('provider', '');
		/** @var string $param_token Token parameter generated */
		$param_token = $request->getQueryParam('token', '');
		/** @var integer $param_error_code Error code */
		$param_error_code = $request->getQueryParam('error_code', '');
		/** @var string $param_error_message Error message */
		$param_error_message = $request->getQueryParam('error_message', '');

		$adapter = null;

		// Either provider or token must be in the request!
		if (empty($param_provider) && empty($param_token)) {
			return $response->withStatus(400)->withJson(['error' => ['message' => 'Missing GET parameters: provider or token']]);
		}

		// Check if provided provider code is valid
		if (!empty($param_provider)) {
			try {
				$this->auth->getProviderConfig($param_provider);
			} catch (InvalidArgumentException $invalidArgumentException) {
				return $response->withStatus(400)->withJson(['error' => ['message' => "Provider '$param_provider' is not found"]]);
			} catch (UnexpectedValueException $unexpectedValueException) {
				return $response->withStatus(501)->withJson(['error' => ['message' => "Provider '$param_provider' is not configured"]]);
			}
		}

		if (!empty($param_error_code) || !empty($param_error_message)) {
			return $response->withStatus(400)->withJson(['error' => ['message' => "Error code '$param_error_code' - $param_error_message"]]);
		}

		/** @var AdapterInterface $adapter */
		$adapter = $this->auth->authenticate($param_provider);
		if ($adapter->isConnected()) {
			$profile = $adapter->getUserProfile();
			$existing_user_id = UserService::findUserIdByEmail($profile->email);
			if (false == $existing_user_id) {
				$new_user_id = UserService::createUserForProfile($profile);
				if (false == $new_user_id) {
					return $response->withStatus(501)->withJson(['error' => ['message' => 'Could not generate new user']]);
				}
			}
		}
		return $response->withStatus(500);
	}
}


// 3rd party social service login
/* $this->app->get('/user/authenticate', function (Request $request, Response $response) {
			$param_provider = $request->getQueryParam('provider', '');
			$param_error_code = $request->getQueryParam('error_code', 0);
			$param_error_msg = $request->getQueryParam('error_message', '');

			$storage = new Session();
			if (!empty($param_provider)) {
				$storage->set('provider', $param_provider);
			}

			if ($param_error_code > 0) {
				// TODO: Add proper template
				echo "Authentication error: $param_error_code <br><br> $param_error_msg <br><br> <a href='/user/authenticate'>Try again</a>";
				exit();
			}

			try {
				if ($provider = $storage->get('provider')) {
					$adapter = $this->auth->authenticate($provider);
					if ($adapter->isConnected()) {
						// Check if user exists and create if not
						$profile = $adapter->getUserProfile();
						$existing_user_id = UserService::findUserId            		ByEmail($profile->email);
						if (false == $existing_user_id) {
							$new_user_id = UserService::createUserForProfile($profile);
							if (false == $new_user_id) {
								// TODO: handle failure
								error_log('Could not generate new user for');
							}
						}
						$storage->set('provider', null);
						return $response->withRedirect('/user');
					}
				}
				// TODO: Throw error
				echo "Authentication error: Provider not saved to session correctly".PHP_EOL;
			} catch (InvalidArgumentException $e) {
				// TODO: Add error logging
				echo "Authentication error: Provider $param_provider is not configured".PHP_EOL;
			} catch (InvalidAuthorizationStateException $e) {
				return $response->withRedirect('/');
			}
			return $response->withRedirect('/');

		})->setName('auth_callback');
*/