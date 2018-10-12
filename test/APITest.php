<?php

use InfamousQ\LManager\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class APITest extends PHPUnit_Framework_TestCase {

    /** @var Slim\App Current instance of Slim application */
    protected $app;

    public function setUp() {
        $this->app = (new App())->getSlim();
    }

    public function testGet() {
        $env = Environment::mock([
            'REQUEST_METHOD'    => 'GET',
            'REQUEST_URI'       => '/api/12',
        ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertJsonStringEqualsJsonString((string) $response->getBody(), json_encode(['message' => 'API GET BY ID 12']));
    }
}