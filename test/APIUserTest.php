<?php

use Slim\Http\Environment;
use Slim\Http\Request;

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
		];
		$container['db'] = function($container) {
			return new \InfamousQ\LManager\Services\PDODatabaseService($container->get('settings')['db']);
		};
		$container['user'] = function($container) {
			return new \InfamousQ\LManager\Services\UserService($container->get('db'));
		};
		$this->container = $container;
	}

	public function tearDown(){
		self::$T->getRollback("test", "0");
	}

	public function testWithoutValidUserDataOwnDataReturn401() {
		$action = new \InfamousQ\LManager\Actions\GetAPIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testWithValidUserDataOwnDataWithStatus200() {
		$new_user_profile = new \Hybridauth\User\Profile();
		$new_user_profile->displayName = 'John Doe';
		$new_user_profile->email = 'john.doe@test.test';
		$new_user_id = $this->container->user->createUserForProfile($new_user_profile);
		$this->assertTrue($new_user_id > 0, 'New user created');

		$action = new \InfamousQ\LManager\Actions\GetAPIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/user',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();
		$request = $request->withAttribute('token', ['user' => ['id' => $new_user_id]]);

		$response = $action($request, $response, []);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => $new_user_id, 'name' => 'John Doe', 'href' => "/api/v1/users/{$new_user_id}/", 'modules' => [], 'layouts' => []]), (string) $response->getBody());
	}

	public function testWithoutValidUserDataOthersDataReturns401() {
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'Molly Doe';
		$existing_user_profile->email = 'molly.doe@test.test';
		$existing_user_id = $this->container->user->createUserForProfile($existing_user_profile);

		$this->assertTrue($existing_user_id > 0, 'New user created');
		$action = new \InfamousQ\LManager\Actions\GetAPIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/user/$existing_user_id/",
		]);
		$request = Request::createFromEnvironment($env);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response, ['id' => $existing_user_id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString( json_encode(['error' => ['message' => 'Invalid token']]), (string) $response->getBody());
	}

	public function testWithValidUserDataOthersDataReturns200() {
		$new_user_profile = new \Hybridauth\User\Profile();
		$new_user_profile->displayName = 'John Doe';
		$new_user_profile->email = 'john.doe@test.test';
		$new_user_id = $this->container->user->createUserForProfile($new_user_profile);
		$this->assertTrue($new_user_id > 0, 'New user created');
		$existing_user_profile = new \Hybridauth\User\Profile();
		$existing_user_profile->displayName = 'James Doe';
		$existing_user_profile->email = 'James.doe@test.test';
		$existing_user_id = $this->container->user->createUserForProfile($existing_user_profile);
		$this->assertTrue($existing_user_id > 0, 'Existing user created');

		$action = new \InfamousQ\LManager\Actions\GetAPIUserAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => "/api/v1/user/$existing_user_id/",
		]);
		$request = Request::createFromEnvironment($env);
		$request = $request->withAttribute('token', ['user' => ['id' => $new_user_id]]);
		$response = new \Slim\Http\Response();

		$response = $action($request, $response, ['id' => $existing_user_id]);
		$this->assertSame(\Slim\Http\StatusCode::HTTP_OK, $response->getStatusCode());
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => $existing_user_id, 'name' => 'James Doe', 'href' => "/api/v1/users/{$existing_user_id}/", 'modules' => [], 'layouts' => []]), (string) $response->getBody());
	}
}