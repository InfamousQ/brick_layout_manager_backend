<?php

namespace InfamousQ\LManager\Actions;

use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use InfamousQ\LManager\Util\Exception;
use Slim\Http\Request;
use Slim\Http\Response;

class GetUserAuthenticateAction {

	/** @var \InfamousQ\LManager\Services\BaseAuthenticationService $auth authentication service*/
	protected $auth;
	/** @var \Slim\Router $router */
	protected $router;
	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service*/
	protected $user_service;
	/** @var \InfamousQ\LManager\Services\TokenServiceInterface $jwt_service */
	protected $jwt_service;

	public function __construct(\Slim\Container $container) {
		$this->auth = $container->get('auth');
		$this->router = $container->get('router');
		$this->user_service = $container->get('user');
		$this->jwt_service = $container->get('jwt');
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
		/** @var string $param_code Code generated */
		$param_code = $request->getQueryParam('code', '');
		/** @var integer $param_error_code Error code */
		$param_error_code = $request->getQueryParam('error_code', '');
		/** @var string $param_error_message Error message */
		$param_error_message = $request->getQueryParam('error_message', '');

		// When we return from integration, provider parameter is not used anymore. Let's save for short term session memory.
		if (!empty($param_provider)) {
			$this->auth->setProviderToStorage($param_provider);
		}

		/** @var AdapterInterface $adapter */
		$adapter = null;

		// Either provider, token or code must be in the request!
		if (empty($param_provider) && empty($param_token) && empty($param_code)) {
			return $response->withStatus(400)->withJson(['error' => ['message' => 'Missing GET parameters: provider or token']]);
		}

		// Check if provided provider code is valid. Required only if code is not given!
		if (!empty($param_provider) && empty($param_code)) {
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

		try {
			$param_provider = $this->auth->getProviderFromStorage();
			if (empty($param_provider)) {
				throw new Exception('Could not retrieve provider from memory');
			}

			$adapter = $this->auth->authenticate($param_provider);
			if ($adapter->isConnected()) {
				$profile = $adapter->getUserProfile();
				// Check if user exists. If not, try to generate new user
				$existing_user_id = $this->user_service->findUserIdByEmail($profile->email);
				if (false == $existing_user_id) {
					$new_user_id = $this->user_service->createUserForProfile($profile);
					if (false == $new_user_id) {
						return $response->withStatus(501)->withJson(['error' => ['message' => 'Could not generate new user']]);
					}
					$existing_user_id = $new_user_id;
				}
				// User is generated, update access_token and return to user
				$access_token = $adapter->getAccessToken()['access_token'];
				$this->user_service->saveAccessTokenForUser($access_token, $this->auth->getProviderType($param_provider), $existing_user_id);
				return $response->withRedirect($request->getUri()->getBasePath() . '/user/token?token=' . $this->jwt_service->generateUserToken($existing_user_id));
			}
			throw new Exception('Could not authenticate');
		} catch (\Exception $e) {
			return $response->withStatus(500)->withJson(['error' => ['message' => "Authentication error: " . $e->getMessage()]]);
		}
	}
}