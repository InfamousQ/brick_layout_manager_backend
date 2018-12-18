<?php

use InfamousQ\LManager\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class AuthenticateTest extends PHPUnit_Framework_TestCase {

	/** @var App Current instance of Lmanager application */
	protected $lmanager;
	/** @var Slim\App Current instance of Slim application */
	protected $app;
	/** @var \InfamousQ\LManager\Services\DummyAuthService */
	protected $dummy_authentication_service;

	public function setUp() {
		$this->lmanager = new App();
		$this->app = $this->lmanager->getSlim();
		$dummy_authentication_service_config = [
			'callback' => 'www.dummy.test',
			'providers' => [
				'test_provider' => [
					'enabled' => true,
					'keys' => [
						'id' => '12345',
						'key' => 'qwert'
					],
				],
			],
		];
		$this->dummy_authentication_service = new \InfamousQ\LManager\Services\DummyAuthService($dummy_authentication_service_config);
	}

	public function testMissingProviderAndMissingTokenReturns400() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->dummy_authentication_service);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
		]);
		$request = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $request;
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => 'Missing GET parameters: provider or token']]));
	}

	public function testGivenProviderIsInvalidReturns400() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->dummy_authentication_service);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=faulty'
		]);
		$request = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $request;
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Provider 'faulty' is not found"]]));
	}

	public function testErrorCodeGivenReturns400WithErrorCodeAndMessage() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->dummy_authentication_service);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=test_provider&error_code=123'
		]);
		$request = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $request;
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Error code '123' - "]]));
	}

	public function testErrorMessageGivenReturns400WithErrorCodeAndMessage() {
		$action = new \InfamousQ\LManager\Actions\GetUserAuthenticateAction($this->dummy_authentication_service);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=test_provider&error_message=error_occured'
		]);
		$request = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $request;
		$response = new \Slim\Http\Response();

		$response = $action($request, $response);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Error code '' - error_occured"]]));
	}
}