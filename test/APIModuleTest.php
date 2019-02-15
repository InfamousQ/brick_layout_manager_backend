<?php

use \InfamousQ\LManager\Actions\APIModuleAction;
use \InfamousQ\LManager\Models\User;
use \InfamousQ\LManager\Models\Module;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class APIModuleTest extends \PHPUnit\Framework\TestCase {

	/** @var \Phinx\Wrapper\TextWrapper $T */
	protected static $T;
	/** @var \Slim\Container $container */
	protected $container;

	protected function setUp() {
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);
		$app->run(new \Symfony\Component\Console\Input\StringInput(' '), new \Symfony\Component\Console\Output\NullOutput());

		self::$T = new \Phinx\Wrapper\TextWrapper($app, array('configuration' => '.deploy/phinx.php'));
		self::$T->getMigrate("test");

		$container = new \Slim\Container();
		$container['settings'] = [
			'db' => [
				'host' => 'bl_db',
				'port' => 5432,
				'dbname' => 'lmanager_test',
				'user' => 'bl_test',
				'password' => 'test',
			],
			'social' => [
				'callback' => 'www.dummy.test',
				'providers' => [
					'test_provider' => [
						'enabled' => true,
						'keys' => [
							'id' => '12345',
							'key' => 'qwert',
						],
						'name' => 'Test provider',
						'code' => 'Test provider code',
						'icon' => 'Test provider icon',
					],
				],
			],
		];
		$container['entity'] = function($container) {
			return new \InfamousQ\LManager\Services\EntityMapperService($container->get('settings')['db']);
		};
		$container['user'] = function($container) {
			return new \InfamousQ\LManager\Services\UserService($container->get('entity'));
		};
		$container['module'] = function($container) {
			return new \InfamousQ\LManager\Services\ModuleService($container->get('entity'));
		};
		$container['auth'] = function($container) {
			return new \InfamousQ\LManager\Services\DummyAuthService($container->get('settings')['social']);
		};
		$this->container = $container;
	}

	public function tearDown(){
		self::$T->getRollback("test", "0");
	}

	public function testNoModuleAtAllReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Annie Doe', 'email' => 'annie.doe@test.test']);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/modules/',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode([]), (string) $response->getBody(), 'No modules set, response is empty');
	}

	public function testAddSingleModuleItShowsUpReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Bernie Doe', 'email' => 'bernie.doe@test.test']);
		$module = $this->container->module->createModule('Test module #1', $user->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/modules/',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$module_json = new stdClass();
		$module_json->id = (int) $module->id;
		$module_json->href = "/api/v1/modules/{$module->id}/";
		$module_json->name = 'Test module #1';
		$module_json->created = $module->created_at->format(\DateTimeInterface::RFC3339);
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$this->assertJsonStringEqualsJsonString( json_encode([$module_json]), (string) $response->getBody(), 'No modules set, response is empty');
	}

	public function testCannotPOSTNewMdoduleWithoutToken() {
		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/modules/',
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Uploaded module #1']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->insert($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Without token, return 401');
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testAddModuleViaRestAndListIt() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Bernie Doe', 'email' => 'bernie.doe@test.test']);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/modules/',
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Uploaded module #1']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['user' => ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->insert($request, $response);
		$module_array = [
			'id' => 1,
			'href' => '/api/v1/modules/1/',
			'name' => 'Uploaded module #1',
		];
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$response_array = array_intersect_key(json_decode((string) $response->getBody(), true), $module_array);
		$this->assertEquals($module_array, $response_array, 'Module saved successfully');
	}
}