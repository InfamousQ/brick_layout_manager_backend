<?php

use InfamousQ\LManager\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class PingTest extends PHPUnit_Framework_TestCase {

    /** @var Slim\App Current instance of Slim application */
    protected $app;

    public function setUp() {
        $this->app = (new App())->getSlim();
    }

    public function testPing() {
		$action = new \InfamousQ\LManager\Actions\GetPingAction(new \Slim\Container());

        $env = Environment::mock([
            'REQUEST_METHOD'    => 'GET',
            'REQUEST_URI'       => '/api/v1/ping',
        ]);
        $request = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $request;
        $response = new \Slim\Http\Response();

        $response = $action($request, $response);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['data' => ['message' => 'pong']]));
    }
}