<?php
use BLMRA\App;
use PHPUnit\Framework\TestCase;

class ModuleControllerTest extends TestCase {

	protected $app;
	protected $request;
	protected $response;

	protected function setUp () {
		$this->app = (new App())->get();
		$this->request = null;
		$this->response = null;
	}

	protected function init ($method, $uri, $qry_string = '') {
		$environment = \Slim\Http\Environment::mock([
			'REQUEST_METHOD' => $method,
			'REQUEST_URI' => $uri,
			'QUERY_STRING' => $qry_string,
		]);
		$this->request = \Slim\Http\Request::createFromEnvironment($environment);
		$this->response = new \Slim\Http\Response();
	}

	public function testViewSingle () {
		$module_id = 33;
		$this->init('GET', '/modules/'.$module_id);
		$this->app->getContainer()['request'] = $this->request;
		$response = $this->app->run(true);
		$this->assertEquals((string) $response->getBody(), json_encode(array('id' => $module_id)));
	}

	public function testEditSingle () {
		$this->assertEquals(1, 1);
	}
}