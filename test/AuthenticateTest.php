<?php

use Slim\Http\Environment;
use Slim\Http\Request;
use Phinx\Wrapper\TextWrapper;

class AuthenticateTest extends \PHPUnit\Framework\TestCase {

	/** @var \Phinx\Wrapper\TextWrapper $T */
	protected static $T;
	/** @var \InfamousQ\LManager\Services\DummyAuthService $dummy_authentication_service */
	protected $dummy_authentication_service;
	/** @var \Slim\Container $container */
	protected $container;

	public function setUp() {
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);
		$app->run(new \Symfony\Component\Console\Input\StringInput(' '), new \Symfony\Component\Console\Output\NullOutput());

		self::$T = new TextWrapper($app, array('configuration' => '.deploy/phinx.php'));
		self::$T->getMigrate("test");

		$container = new \Slim\Container();
		$container['settings'] = [
			'social' => [
				'callback' => 'www.dummy.test',
				'providers' => [
					'test_provider' => [
						'enabled' => true,
						'keys' => [
							'id' => '12345',
							'key' => 'qwert',
						],
					],
				],
				'pass_next_usage' => false,
			],
			'db' => [
				'host' => 'bl_db',
				'port' => 5432,
				'dbname' => 'lmanager_test',
				'user' => 'bl_test',
				'password' => 'test',
			],
		];
		$container['db'] = function($container) {
			return new \InfamousQ\LManager\Services\PDODatabaseService($container->get('settings')['db']);
		};
		$container['user'] = function($container) {
			return new \InfamousQ\LManager\Services\UserService($container->get('db'));
		};
		$container['auth'] = function($container) {
			return new \InfamousQ\LManager\Services\DummyAuthService($container->get('settings')['social']);
		};
		$this->container = $container;
	}

	public function tearDown(){
		self::$T->getRollback("test");
	}

	public function testMissingProviderAndMissingTokenReturns400() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame(400, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => 'Missing GET parameters: provider or token']]));
	}

	public function testGivenProviderIsInvalidReturns400() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=faulty'
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame(400, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Provider 'faulty' is not found"]]));
	}

	public function testErrorCodeGivenReturns400WithErrorCodeAndMessage() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=test_provider&error_code=123'
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame(400, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Error code '123' - "]]));
	}

	public function testErrorMessageGivenReturns400WithErrorCodeAndMessage() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=test_provider&error_message=error_occured'
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString(json_encode(['error' => ['message' => "Error code '' - error_occured"]]), (string) $response->getBody());
	}

	public function testSuccessfulAuthenticationReturns200WithToken() {
		$this->container->get('auth')->setProviderToStorage('test_provider');
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->container);
		$env = Environment::mock([
			'REQUEST_MEHOD'     => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'code=AUTHENTICATION_OK&state=APP_STATE_OK',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['data' => ['token' => 'OAUTH_DUMMY_TOKEN']]), (string) $response->getBody());
	}

}