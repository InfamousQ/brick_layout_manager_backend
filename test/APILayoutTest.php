<?php

use \InfamousQ\LManager\Actions\APILayoutAction;
use InfamousQ\LManager\Models\Layout;
use InfamousQ\LManager\Models\Module;
use \InfamousQ\LManager\Models\User;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class APILayoutTest extends \PHPUnit\Framework\TestCase {


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
		$this->container->entity->closeConnectionToDB();
	}

	public function testNoLayoutsAtAllReturns200() {
		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/layouts/',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode([]), (string) $response->getBody());
	}

	public function testPrivateAndPublicLayoutsReturnOnlyPublicWithoutToken() {
		// Set up one private layout for user1, one private layout for user2 and one public layout
		/** @var User $user1 */
		$user1 = $this->container->user->createUserFromArray(['name' => 'Arther Doe', 'email' => 'arthur.doe@test.test']);
		/** @var User $user2 */
		$user2 = $this->container->user->createUserFromArray(['name' => 'Bob Doe', 'email' => 'bob.doe@test.test']);
		/** @var Layout $layout_private_user1 */
		$layout_private_user1 = $this->container->module->createLayout('Private layout for user 1', $user1->id);
		/** @var Layout $layout_private_user2 */
		$layout_private_user2 = $this->container->module->createLayout('Private layout for user 2', $user2->id);
		/** @var Layout $layout_public_user1 */
		$layout_public_user1 = $this->container->module->createLayout('Public layout for user 1', $user1->id);
		$layout_public_user1->public = true;
		$this->container->module->saveLayout($layout_public_user1);
		$this->assertSame(count($this->container->module->getLayouts()), 3);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/layouts/',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout_public_user1->id;
		$layout_json->href = "/api/v1/layouts/{$layout_public_user1->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout_public_user1->id}/";
		$layout_json->name = 'Public layout for user 1';
		$layout_json->public = $layout_public_user1->public;
		$layout_json->created = $layout_public_user1->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $user1->id;
		$layout_json->author->name = $user1->name;
		$layout_json->author->href = "/api/v1/users/{$user1->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString( json_encode([$layout_json]),  (string) $response->getBody());
	}

	public function testPrivateAndPublicLayoutsReturnPublicAndOwnPrivateWithToken() {
		// Set up one private layout for user1, one private layout for user2 and one public layout
		/** @var User $user1 */
		$user1 = $this->container->user->createUserFromArray(['name' => 'Carl Doe', 'email' => 'carl.doe@test.test']);
		/** @var User $user2 */
		$user2 = $this->container->user->createUserFromArray(['name' => 'Dennis Doe', 'email' => 'dennis.doe@test.test']);
		/** @var Layout $layout_private_user1 */
		$layout_private_user1 = $this->container->module->createLayout('Private layout for user 1', $user1->id, 64, 20);
		/** @var Layout $layout_private_user2 */
		$layout_private_user2 = $this->container->module->createLayout('Private layout for user 2', $user2->id);
		/** @var Layout $layout_public_user1 */
		$layout_public_user1 = $this->container->module->createLayout('Public layout for user 1', $user1->id);
		$layout_public_user1->public = true;
		$this->container->module->saveLayout($layout_public_user1);
		$this->assertSame(count($this->container->module->getLayouts()), 3);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/layouts/',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $user1->id]]);
		$response = new Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$public_layout_json = new stdClass();
		$public_layout_json->id = (int) $layout_public_user1->id;
		$public_layout_json->href = "/api/v1/layouts/{$layout_public_user1->id}/";
		$public_layout_json->image_href = "/svg/layouts/{$layout_public_user1->id}/";
		$public_layout_json->name = 'Public layout for user 1';
		$public_layout_json->public = $layout_public_user1->public;
		$public_layout_json->created = $layout_public_user1->created_at->format(\DateTimeInterface::RFC3339);
		$public_layout_json->author = new stdClass();
		$public_layout_json->author->id = (int) $user1->id;
		$public_layout_json->author->name = $user1->name;
		$public_layout_json->author->href = "/api/v1/users/{$user1->id}/";
		$public_layout_json->modules = [];
		$private_layout_json = new stdClass();
		$private_layout_json->id = (int) $layout_private_user1->id;
		$private_layout_json->href = "/api/v1/layouts/{$layout_private_user1->id}/";
		$private_layout_json->image_href = "/svg/layouts/{$layout_private_user1->id}/";
		$private_layout_json->name = 'Private layout for user 1';
		$private_layout_json->public = $layout_private_user1->public;
		$private_layout_json->created = $layout_private_user1->created_at->format(\DateTimeInterface::RFC3339);
		$private_layout_json->author = new stdClass();
		$private_layout_json->author->id = (int) $user1->id;
		$private_layout_json->author->name = $user1->name;
		$private_layout_json->author->href = "/api/v1/users/{$user1->id}/";
		$private_layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString( json_encode([$private_layout_json, $public_layout_json]),  (string) $response->getBody());
	}

	public function testFetchSingleLayoutWithoutValidIdReturns404() {
		$this->assertLessThan( 1, count($this->container->module->getLayouts()));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/layouts/1/',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchSingle($request, $response, ['id' => 1]);
		$this->assertSame(StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Layout not found']]), (string) $response->getBody());
	}

	public function testFetchSinglePrivateLayoutWithoutTokenReturns401() {
		/** @var User $user1 */
		$user = $this->container->user->createUserFromArray(['name' => 'Elisa Doe', 'email' => 'elisa.doe@test.test']);
		/** @var Layout $layout_private */
		$layout_private = $this->container->module->createLayout('Private layout for user', $user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout_private->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchSingle($request, $response, ['id' => $layout_private->id]);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testFetchSinglePublicLayoutWithoutTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Frank Doe', 'email' => 'frank.doe@test.test']);
		/** @var Layout $layout_public */
		$layout_public = $this->container->module->createLayout('Public layout for user', $user->id);
		$layout_public->public = true;
		$this->container->module->saveLayout($layout_public);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout_public->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchSingle($request, $response, ['id' => $layout_public->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout_public->id;
		$layout_json->href = "/api/v1/layouts/{$layout_public->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout_public->id}/";
		$layout_json->name = 'Public layout for user';
		$layout_json->public = $layout_public->public;
		$layout_json->w = $layout_public->w;
		$layout_json->h = $layout_public->h;
		$layout_json->created = $layout_public->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $user->id;
		$layout_json->author->name = $user->name;
		$layout_json->author->href = "/api/v1/users/{$user->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json), (string) $response->getBody());
	}

	public function testFetchingSinglePrivateLayoutWithTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Henry Doe', 'email' => 'Henry.doe@test.test']);
		/** @var Layout $layout_private */
		$layout_private = $this->container->module->createLayout('Private layout for user', $user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout_private->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $user->id]]);
		$response = new Response();

		$response = $action->fetchSingle($request, $response, ['id' => $layout_private->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout_private->id;
		$layout_json->href = "/api/v1/layouts/{$layout_private->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout_private->id}/";
		$layout_json->name = 'Private layout for user';
		$layout_json->public = $layout_private->public;
		$layout_json->w = $layout_private->w;
		$layout_json->h = $layout_private->h;
		$layout_json->created = $layout_private->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $user->id;
		$layout_json->author->name = $user->name;
		$layout_json->author->href = "/api/v1/users/{$user->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json), (string) $response->getBody());
	}

	public function testFetchingSinglePublicLayoutWithTokenReturns200() {
		/** @var User $active_user */
		$active_user = $this->container->user->createUserFromArray(['name' => 'Ivan Doe', 'email' => 'ivan.doe@test.test']);
		/** @var User $authoring_user */
		$authoring_user = $this->container->user->createUserFromArray(['name' => 'John Doe', 'email' => 'john.doe@test.test']);
		/** @var Layout $layout_public */
		$layout_public = $this->container->module->createLayout('Public layout for authoring user', $authoring_user->id, 14);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout_public->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $active_user->id]]);
		$response = new Response();

		$response = $action->fetchSingle($request, $response, ['id' => $layout_public->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout_public->id;
		$layout_json->href = "/api/v1/layouts/{$layout_public->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout_public->id}/";
		$layout_json->name = 'Public layout for authoring user';
		$layout_json->public = $layout_public->public;
		$layout_json->w = $layout_public->w;
		$layout_json->h = $layout_public->h;
		$layout_json->created = $layout_public->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $authoring_user->id;
		$layout_json->author->name = $authoring_user->name;
		$layout_json->author->href = "/api/v1/users/{$authoring_user->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json), (string) $response->getBody());
	}

	public function testPOSTLayoutWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Kevin Doe', 'email' => 'kevin.doe@test.test']);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/layouts/',
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'New layout']));
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->insert($request, $response);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testPOSTLayoutWithTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Kevin Doe', 'email' => 'kevin.doe@test.test']);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/layouts/',
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'New layout']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->insert($request, $response);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_array = [
			'id' => 1,
			'href' => '/api/v1/layouts/1/',
			'name' => 'New layout',
		];
		$response_array = array_intersect_key(json_decode((string) $response->getBody(), true), $layout_array);
		$this->assertEquals($layout_array, $response_array, 'Module saved successfully');
	}

	public function testEditExistingLayoutWithPOSTWithTokenReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Larry Doe', 'email' => 'larry.doe@test.test']);
		/** @var Layout $existing_layout */
		$existing_layout = $this->container->module->createLayout('Existing layout before edit', $user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'PUT',
			'REQUEST_URI'       => "/api/v1/layouts/{$existing_layout->id}/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Existing layout after edit']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->editSingle($request, $response, ['id' => $existing_layout->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_array = [
			'id' => 1,
			'href' => '/api/v1/layouts/1/',
			'name' => 'Existing layout after edit',
		];
		$response_array = array_intersect_key(json_decode((string) $response->getBody(), true), $layout_array);
		$this->assertEquals($layout_array, $response_array, 'Module saved successfully');
	}

	public function testDELETENonExistingLayoutReturns404() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Mona Doe', 'email' => 'mona.doe@test.test']);
		$this->assertSame(0, count($this->container->module->getLayouts()));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'DELETE',
			'REQUEST_URI'       => "/api/v1/layouts/{1/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->deleteSingle($request, $response, ['id' => 1]);
		$this->assertSame(StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Layout not found']]), (string) $response->getBody());
	}

	public function testDELETELayoutWithoutTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Mona Doe', 'email' => 'mona.doe@test.test']);
		/** @var Layout $existing_layout */
		$existing_layout = $this->container->module->createLayout('Existing layout before edit', $user->id);
		$this->assertSame(1, count($this->container->module->getLayouts()));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'DELETE',
			'REQUEST_URI'       => "/api/v1/layouts/{$existing_layout->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->deleteSingle($request, $response, ['id' => $existing_layout->id]);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testDELETELayoutWithoutBeingOwnerReturns401() {
		/** @var User $authoring_user */
		$authoring_user = $this->container->user->createUserFromArray(['name' => 'Neil Doe', 'email' => 'neil.doe@test.test']);
		/** @var User $active_user */
		$active_user = $this->container->user->createUserFromArray(['name' => 'Oliver Doe', 'email' => 'oliver.doe@test.test']);
		/** @var Layout $existing_layout */
		$existing_layout = $this->container->module->createLayout('Existing layout before edit', $authoring_user->id);
		$this->assertSame(1, count($this->container->module->getLayouts()));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'DELETE',
			'REQUEST_URI'       => "/api/v1/layouts/{$existing_layout->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $active_user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->deleteSingle($request, $response, ['id' => $existing_layout->id]);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testDELETEOwnedLayoutReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Paul Doe', 'email' => 'paul.doe@test.test']);
		/** @var Layout $existing_layout */
		$existing_layout = $this->container->module->createLayout('Existing layout before edit', $user->id);
		$this->assertSame(1, count($this->container->module->getLayouts()));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'DELETE',
			'REQUEST_URI'       => "/api/v1/layouts/{$existing_layout->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->deleteSingle($request, $response, ['id' => $existing_layout->id]);
		$this->assertSame(StatusCode::HTTP_NO_CONTENT, $response->getStatusCode());
		$this->assertSame( json_encode("") , (string) $response->getBody());
		$this->assertSame(0, count($this->container->module->getLayouts()));
	}

	public function testAddingModuleToSomeLayoutWithTokenReturns401() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Paul Doe', 'email' => 'paul.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Layout for user', $user->id);
		/** @var Module $new_module */
		$new_module = $this->container->module->createModule('Test module #1', $user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout->id}/modules/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['id' => $new_module->id, 'x' => 12, 'y' => 34]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->addModuleToLayout($request, $response, ['id' => $layout->id]);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testAddingModuleToSomeLayoutWithoutTokenReturns401() {
		/** @var User $author_user */
		$author_user = $this->container->user->createUserFromArray(['name' => 'Quentin Doe', 'email' => 'quentin.doe@test.test']);
		/** @var User $active_user */
		$active_user = $this->container->user->createUserFromArray(['name' => 'Ron Doe', 'email' => 'ron.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Layout for user', $author_user->id);
		/** @var Module $new_module */
		$new_module = $this->container->module->createModule('Test module #1', $author_user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout->id}/modules/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['id' => $new_module->id, 'x' => 12, 'y' => 34]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $active_user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->addModuleToLayout($request, $response, ['id' => $layout->id]);
		$this->assertSame(StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testAddOwnModuleToOwnedLayoutReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Simon Doe', 'email' => 'simon.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Layout for user', $user->id);
		/** @var Module $new_module */
		$new_module = $this->container->module->createModule('Test module #1', $user->id);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout->id}/modules/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['id' => $new_module->id, 'x' => 12, 'y' => 34]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->addModuleToLayout($request, $response, ['id' => $layout->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout->id;
		$layout_json->href = "/api/v1/layouts/{$layout->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout->id}/";
		$layout_json->name = 'Layout for user';
		$layout_json->public = $layout->public;
		$layout_json->w = $layout->w;
		$layout_json->h = $layout->h;
		$layout_json->created = $layout->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $user->id;
		$layout_json->author->name = $user->name;
		$layout_json->author->href = "/api/v1/users/{$user->id}/";
		$layout_json->modules = [];
		$module_json = new stdClass();
		$module_json->id = (int) $new_module->id;
		$module_json->href = "/api/v1/modules/{$new_module->id}/";
		$module_json->name = $new_module->name;
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$module_json->x = 12;
		$module_json->y = 34;
		$module_json->created = $new_module->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->modules[] = $module_json;
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json) , (string) $response->getBody());
	}

	public function testAddSomeModuleToOwnedLayoutReturns200() {
		/** @var User $author_user */
		$author_user = $this->container->user->createUserFromArray(['name' => 'Tim Doe', 'email' => 'tim.doe@test.test']);
		/** @var User $active_user */
		$active_user = $this->container->user->createUserFromArray(['name' => 'Uma Doe', 'email' => 'uma.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Layout for active user', $active_user->id);
		/** @var Module $new_module */
		$new_module = $this->container->module->createModule('Test module #1', $author_user->id);
		// Note: new module is by default public, change to private since adding private modules is okey too
		$new_module->public = false;
		$this->container->module->saveModule($new_module);

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout->id}/modules/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['id' => $new_module->id, 'x' => 12, 'y' => 34]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $active_user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->addModuleToLayout($request, $response, ['id' => $layout->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout->id;
		$layout_json->href = "/api/v1/layouts/{$layout->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout->id}/";
		$layout_json->name = 'Layout for active user';
		$layout_json->public = $layout->public;
		$layout_json->w = $layout->w;
		$layout_json->h = $layout->h;
		$layout_json->created = $layout->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $active_user->id;
		$layout_json->author->name = $active_user->name;
		$layout_json->author->href = "/api/v1/users/{$active_user->id}/";
		$layout_json->modules = [];
		$module_json = new stdClass();
		$module_json->id = (int) $new_module->id;
		$module_json->href = "/api/v1/modules/{$new_module->id}/";
		$module_json->name = $new_module->name;
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $author_user->id;
		$module_json->author->name = $author_user->name;
		$module_json->author->href = "/api/v1/users/{$author_user->id}/";
		$module_json->x = 12;
		$module_json->y = 34;
		$module_json->created = $new_module->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->modules[] = $module_json;
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json) , (string) $response->getBody());
	}

	public function testUpdateOwnedLayoutsOwnedModuleReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Vince Doe', 'email' => 'vince.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Existing layout', $user->id);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #1', $user->id);
		$this->container->module->connectModuleToLayout($layout, $module, 11, 22);
		$this->assertSame(1, count($layout->modules));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'PUT',
			'REQUEST_URI'       => "/api/v1/layouts/{$layout->id}/modules/{$module->id}/",
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['x' => 12 , 'y' => 23]));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['data' => (object) ['id' => $user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$response = $action->editModuleInLayout($request, $response, ['id' => $layout->id, 'module_id' => (int) $module->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout->id;
		$layout_json->href = "/api/v1/layouts/{$layout->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout->id}/";
		$layout_json->name = 'Existing layout';
		$layout_json->public = $layout->public;
		$layout_json->w = $layout->w;
		$layout_json->h = $layout->h;
		$layout_json->created = $layout->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int) $user->id;
		$layout_json->author->name = $user->name;
		$layout_json->author->href = "/api/v1/users/{$user->id}/";
		$layout_json->modules = [];
		$module_json = new stdClass();
		$module_json->id = (int) $module->id;
		$module_json->href = "/api/v1/modules/{$module->id}/";
		$module_json->name = $module->name;
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$module_json->x = 12;
		$module_json->y = 23;
		$module_json->created = $module->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->modules[] = $module_json;
		$this->assertJsonStringEqualsJsonString( json_encode($layout_json) , (string) $response->getBody());
	}

	public function testDELETEOwnedLayoutsOwnedModuleReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Annie Doe', 'email' => 'annie.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Existing layout', $user->id);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module #1', $user->id);
		$this->container->module->connectModuleToLayout($layout, $module, 11, 22);
		$this->assertSame(1, count($layout->modules));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD' => 'DELETE',
			'REQUEST_URI' => "/api/v1/layouts/{$layout->id}/modules/{$module->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object)['id' => $user->id]]);
		$response = new Response();

		$response = $action->deleteModuleInLayout($request, $response, ['id' => $layout->id, 'module_id' => (int)$module->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int)$layout->id;
		$layout_json->href = "/api/v1/layouts/{$layout->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout->id}/";
		$layout_json->name = 'Existing layout';
		$layout_json->public = $layout->public;
		$layout_json->w = $layout->w;
		$layout_json->h = $layout->h;
		$layout_json->created = $layout->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int)$user->id;
		$layout_json->author->name = $user->name;
		$layout_json->author->href = "/api/v1/users/{$user->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString(json_encode($layout_json), (string)$response->getBody());
	}

	public function testDELETEOwnedLayoutsSomeModuleReturns200() {
		/** @var User $active_user */
		$active_user = $this->container->user->createUserFromArray(['name' => 'Burt Doe', 'email' => 'burt.doe@test.test']);
		/** @var User $author_user */
		$author_user = $this->container->user->createUserFromArray(['name' => 'Carl Doe', 'email' => 'carl.doe@test.test']);
		/** @var Layout $layout */
		$layout = $this->container->module->createLayout('Burt\'s layout', $active_user->id);
		/** @var Module $module */
		$module = $this->container->module->createModule('Test module', $author_user->id);
		$this->container->module->connectModuleToLayout($layout, $module, 11, 22);
		$this->assertSame(1, count($layout->modules));

		$action = new APILayoutAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD' => 'DELETE',
			'REQUEST_URI' => "/api/v1/layouts/{$layout->id}/modules/{$module->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object)['id' => $active_user->id]]);
		$response = new Response();

		$response = $action->deleteModuleInLayout($request, $response, ['id' => $layout->id, 'module_id' => (int)$module->id]);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$layout_json = new stdClass();
		$layout_json->id = (int) $layout->id;
		$layout_json->href = "/api/v1/layouts/{$layout->id}/";
		$layout_json->image_href = "/svg/layouts/{$layout->id}/";
		$layout_json->name = 'Burt\'s layout';
		$layout_json->public = $layout->public;
		$layout_json->w = $layout->w;
		$layout_json->h = $layout->h;
		$layout_json->created = $layout->created_at->format(\DateTimeInterface::RFC3339);
		$layout_json->author = new stdClass();
		$layout_json->author->id = (int)$active_user->id;
		$layout_json->author->name = $active_user->name;
		$layout_json->author->href = "/api/v1/users/{$active_user->id}/";
		$layout_json->modules = [];
		$this->assertJsonStringEqualsJsonString(json_encode($layout_json), (string)$response->getBody());
	}
}