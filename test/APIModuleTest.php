<?php

use \InfamousQ\LManager\Actions\APIModuleAction;
use \InfamousQ\LManager\Models\User;
use \InfamousQ\LManager\Models\Module;
use \InfamousQ\Lmanager\Models\Plate;
use \InfamousQ\Lmanager\Models\Color;
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
		$this->assertJsonStringEqualsJsonString( json_encode([$module_json]), (string) $response->getBody(), 'Module set, found from response');
	}

	public function testCreatePOSTNewMdoduleWithoutToken() {
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

		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/modules/',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchList($request, $response);
		$saved_module = $this->container->module->getModuleById($module_array['id']);
		$module_json = new stdClass();
		$module_json->id = (int) $saved_module->id;
		$module_json->href = "/api/v1/modules/{$saved_module->id}/";
		$module_json->name = 'Uploaded module #1';
		$module_json->created = $saved_module->created_at->format(\DateTimeInterface::RFC3339);
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode([$module_json]), (string) $response->getBody(), 'Module shows in list' );
	}

	public function testViewSingleModuleWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Cecil Doe', 'email' => 'cecil.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #3', $user->id);
		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSingle($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testViewSingleModuleWithTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Danny Doe', 'email' => 'danny.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #4', $user->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSingle($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$module_json = new stdClass();
		$module_json->id = (int) $module->id;
		$module_json->href = "/api/v1/modules/{$module->id}/";
		$module_json->name = 'Test module #4';
		$module_json->public = true;
		$module_json->created = $module->created_at->format(\DateTimeInterface::RFC3339);
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$this->assertJsonStringEqualsJsonString( json_encode($module_json), (string) $response->getBody(), 'Module set, found from response');
	}

	public function testEditingSingleModuleWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Fred Doe', 'email' => 'fred.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #5', $user->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'PUT',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Uploaded module #2']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->editSingle($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testEditingSingleModuleWithTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Gary Doe', 'email' => 'gary.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #6', $user->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'PUT',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Uploaded module #3']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['user' => ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->editSingle($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$edited_module_json = new stdClass();
		$edited_module_json->id = (int) $module->id;
		$edited_module_json->href = "/api/v1/modules/{$module->id}/";
		$edited_module_json->name = 'Uploaded module #3';
		$edited_module_json->public = true;
		$edited_module_json->created = $module->created_at->format(\DateTimeInterface::RFC3339);
		$edited_module_json->author = new stdClass();
		$edited_module_json->author->id = (int) $user->id;
		$edited_module_json->author->name = $user->name;
		$edited_module_json->author->href = "/api/v1/users/{$user->id}/";
		$this->assertJsonStringEqualsJsonString( json_encode($edited_module_json), (string) $response->getBody());
	}

	public function testFetchingModulesPlatesWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Henry Doe', 'email' => 'henry.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #7', $user->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/plates/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSinglePlates($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testFetchingModulesPlatesWithInvalidModuleIdReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Larry Doe', 'email' => 'larry.doe@test.test']);
		$invalid_module_id = 9999;

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/modules/{$invalid_module_id}/plates/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSinglePlates($request, $response, ['id' => $invalid_module_id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Module not found']]), (string) $response->getBody());
	}

	public function testFetchingModulesPlatesWithValidDataReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Mary Doe', 'email' => 'mary.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #8', $user->id);
		/** @var Color $white_color */
		$white_color = $this->container->module->createColor('White', 'FFFFFF');
		/** @var Color $black_color */
		$black_color = $this->container->module->createColor('Black', '000000');
		/** @var Plate $white_plate */
		$white_plate = $this->container->module->createPlate(1, 2, 3, 4, 5, $white_color->id, $module->id);
		/** @var Plate $black_plate */
		$black_plate = $this->container->module->createPlate(11, 22, 33, 44, 55, $black_color->id, $module->id);

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/plates/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSinglePlates($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$white_plate_json = new stdClass();
		$white_plate_json->id = (int) $white_plate->id;
		$white_plate_json->x = 1;
		$white_plate_json->y = 2;
		$white_plate_json->z = 3;
		$white_plate_json->h = 4;
		$white_plate_json->w = 5;
		$white_plate_json->color = new stdClass();
		$white_plate_json->color->id = (int) $white_color->id;
		$white_plate_json->color->name = 'White';
		$white_plate_json->color->hex = 'FFFFFF';
		$black_plate_json = new stdClass();
		$black_plate_json->id = (int) $black_plate->id;
		$black_plate_json->x = 11;
		$black_plate_json->y = 22;
		$black_plate_json->z = 33;
		$black_plate_json->h = 44;
		$black_plate_json->w = 55;
		$black_plate_json->color = new stdClass();
		$black_plate_json->color->id = (int) $black_color->id;
		$black_plate_json->color->name = 'Black';
		$black_plate_json->color->hex = '000000';
		$plates_json = [$white_plate_json, $black_plate_json];
		$this->assertJsonStringEqualsJsonString( json_encode($plates_json), (string) $response->getBody());
	}

	public function testPOSTModulesPlatesWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Niles Doe', 'email' => 'niles.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #9', $user->id);
		$white_color = $this->container->module->createColor('White', '000000');

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/plates/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['x' => 31, 'y' => 32, 'z' => 33, 'h' => 34, 'w' => 35, 'color' => $white_color->id]));
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();
		$response = $response->withBody($new_request_body);

		$response = $action->addPlate($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testPOSTModulesPlatesWithInvalidModuleIdReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Olivia Doe', 'email' => 'Olivia.doe@test.test']);
		$invalid_module_id = 9999;
		$white_color = $this->container->module->createColor('White', '000000');

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/modules/{$invalid_module_id}/plates/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['x' => 31, 'y' => 32, 'z' => 33, 'h' => 34, 'w' => 35, 'color' => $white_color->id]));
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetchSinglePlates($request, $response, ['id' => $invalid_module_id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Module not found']]), (string) $response->getBody());
	}

	public function testPOSTModulesPlatesWithValidDataReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Peter Doe', 'email' => 'peter.doe@test.test']);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #10', $user->id);
		$white_color = $this->container->module->createColor('White', '000000');

		$action = new APIModuleAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/modules/{$module->id}/plates/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['x' => 31, 'y' => 32, 'z' => 33, 'h' => 34, 'w' => 35, 'color' => $white_color->id]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['user' => ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new \Slim\Http\Response();

		$response = $action->addPlate($request, $response, ['id' => $module->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$plate_json = new stdClass();
		$plate_json->id = 1;
		$plate_json->x = 31;
		$plate_json->y = 32;
		$plate_json->z = 33;
		$plate_json->h = 34;
		$plate_json->w = 35;
		$plate_json->color = new stdClass();
		$plate_json->color->id = (int) $white_color->id;
		$plate_json->color->name = 'White';
		$plate_json->color->hex = '000000';
		$this->assertJsonStringEqualsJsonString( json_encode($plate_json), (string) $response->getBody());
	}
}