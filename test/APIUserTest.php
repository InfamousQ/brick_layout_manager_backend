<?php

use Slim\Http\Environment;
use Slim\Http\Request;

use InfamousQ\LManager\Models\User;

class APIUserTest extends \PHPUnit\Framework\TestCase {

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

	public function testWithoutValidUserDataOwnDataReturn401() {
		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->fetch($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testWithValidUserDataOwnDataWithStatus200() {
		$new_user_profile = new \Hybridauth\User\Profile();
		$new_user_profile->displayName = 'John Doe';
		$new_user_profile->email = 'john.doe@test.test';
		/** @var User $new_user */
		$new_user = $this->container->user->createUserForProfile($new_user_profile);
		$this->assertTrue($new_user->id > 0, 'New user created');

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $new_user->id]]);

		$response = $action->fetch($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => $new_user->id, 'name' => 'John Doe', 'href' => "/api/v1/users/{$new_user->id}/", 'modules' => [], 'layouts' => []]), (string) $response->getBody());
	}

	public function testWithoutValidUserDataOthersDataReturns401() {
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'Molly Doe';
		$existing_user_profile->email = 'molly.doe@test.test';
		/** @var User $existing_user */
		$existing_user = $this->container->user->createUserForProfile($existing_user_profile);

		$this->assertTrue($existing_user->id > 0, 'New user created');
		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/user/{$existing_user->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->fetch($request, $response, ['id' => $existing_user->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testWithValidUserDataOthersDataReturns200() {
		$new_user_profile = new \Hybridauth\User\Profile();
		$new_user_profile->displayName = 'John Doe';
		$new_user_profile->email = 'john.doe@test.test';
		/** @var User $new_user */
		$new_user = $this->container->user->createUserForProfile($new_user_profile);
		$this->assertTrue($new_user->id > 0, 'New user created');
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'James Doe';
		$existing_user_profile->email = 'James.doe@test.test';
		/** @var User $existing_user */
		$existing_user = $this->container->user->createUserForProfile($existing_user_profile);
		$this->assertTrue($existing_user->id > 0, 'Existing user created');

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/user/{$existing_user->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['data' => (object) ['id' => $existing_user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetch($request, $response, ['id' => $existing_user->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => $existing_user->id, 'name' => 'James Doe', 'href' => "/api/v1/users/{$existing_user->id}/", 'modules' => [], 'layouts' => []]), (string) $response->getBody());
	}

	public function testUpdatingUserWhenNoTokenPresentReturns401() {
		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action->update($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testUpdatingUserWithoutIdInPathReturns400() {
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'Dean Doe';
		$existing_user_profile->email = 'dean.doe@test.test';
		/** @var User $existing_user */
		$existing_user = $this->container->user->createUserForProfile($existing_user_profile);

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $existing_user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->update($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid user id']]), (string) $response->getBody());
	}

	public function testUpdatingUserWithInvalidIdInPathReturns404() {
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'Peter Doe';
		$existing_user_profile->email = 'peter.doe@test.test';
		/** @var User $existing_user */
		$existing_user = $this->container->user->createUserForProfile($existing_user_profile);

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $existing_user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->update($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid user id']]), (string) $response->getBody());
	}

	public function testUpdatingUserReturns200() {
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'Aaron Doe';
		$existing_user_profile->email = 'Aaron.doe@test.test';
		/** @var User $existing_user */
		$existing_user = $this->container->user->createUserForProfile($existing_user_profile);

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/user/'.$existing_user->id.'/',
		]);
		$new_request_body = new \Slim\Http\RequestBody();
		$new_request_body->write(json_encode(['name' => 'Aaron "Test guy" Doe']));
		$request = Request::createFromEnvironment($env);
		$request = $request
			->withAttribute('token', ['user' => ['id' => $existing_user->id]])
			->withBody($new_request_body)
			->withHeader('Content-Type', 'application/json');
		$response = new \Slim\Http\Response();

		$response = $action->update($request, $response, ['id' => $existing_user->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => $existing_user->id, 'name' => 'Aaron "Test guy" Doe', 'href' => "/api/v1/users/{$existing_user->id}/", 'modules' => [], 'layouts' => []]), (string) $response->getBody());
	}

	public function testGetAvailableUsersReturns200() {
		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'POST',
			'REQUEST_URI'       => '/api/v1/user/providers/',
		]);

		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();
		$response = $action->providers($request, $response);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$provider_info = (object) [
			'name' => 'Test provider',
			'code' => 'Test provider code',
			'icon' => 'Test provider icon',
			];
		$this->assertJsonStringEqualsJsonString(json_encode([$provider_info]), (string) $response->getBody());
	}

	public function testcreateUserWithModuleAndShowModuleListReturns200() {
		/** @var User user */
		$user = $this->container->user->createUserFromArray(['name' => 'Annie Doe', 'email' => 'annie.doe@test.test']);
		/** @var \InfamousQ\LManager\Models\Module $module */
		$module = $this->container->module->createModule('Test module #1', $user->id);

		$action = new \InfamousQ\LManager\Actions\APIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/user/{$user->id}/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $user->id]]);
		$response = new \Slim\Http\Response();

		$response = $action->fetch($request, $response, ['id' => $user->id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$module_json = new stdClass();
		$module_json->id = (int) $module->id;
		$module_json->href = "/api/v1/modules/{$module->id}/";
		$module_json->image_href = "/svg/modules/{$module->id}/";
		$module_json->name = 'Test module #1';
		$module_json->created = $module->created_at->format(\DateTimeInterface::RFC3339);
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$module_json->public = (bool) $module->public;
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => (int) $user->id, 'name' => 'Annie Doe', 'href' => "/api/v1/users/{$user->id}/", 'modules' => [$module_json], 'layouts' => []]), (string) $response->getBody());
	}
}