<?php

use InfamousQ\LManager\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class AuthenticateTest extends PHPUnit_Framework_TestCase {

	/** @var App Current instance of Lmanager application */
	protected $lmanager;
	/** @var Slim\App Current instance of Slim application */
	protected $app;

	public function setUp() {
		$this->lmanager = new App();
		$this->app = $this->lmanager->getSlim();
	}

	public function testMissingProviderAndMissingTokenReturns400() {
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
		]);
		$req = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $req;
		$response = $this->app->run(true);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => 'Missing GET parameters: provider or token']]));
	}

	public function testGivenProviderIsInvalidReturns400() {
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=faulty'
		]);
		$req = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $req;
		$response = $this->app->run(true);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Provider 'faulty' is not found"]]));
	}

	public function testErrorCodeGivenReturns400WithErrorCodeAndMessage() {
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=Facebook&error_code=123'
		]);
		$req = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $req;
		$response = $this->app->run(true);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Error code '123' - "]]));
	}

	public function testErrorMessageGivenReturns400WithErrorCodeAndMessage() {
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user/authenticate',
			'QUERY_STRING'      => 'provider=Facebook&error_message=error_occured'
		]);
		$req = Request::createFromEnvironment($env);
		$this->app->getContainer()['request'] = $req;
		$response = $this->app->run(true);
		$this->assertSame($response->getStatusCode(), 400);
		$this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['error' => ['message' => "Error code '' - error_occured"]]));
	}
}