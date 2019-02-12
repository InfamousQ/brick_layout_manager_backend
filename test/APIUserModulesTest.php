<?php

use Slim\Http\Environment;
use Slim\Http\Request;

use InfamousQ\LManager\Models\User;
use InfamousQ\LManager\Models\Module;

class APIUserModulesTest extends \PHPUnit\Framework\TestCase {

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
		$container['module'] = function($container) {
			return new \InfamousQ\LManager\Services\ModuleService($container->get('entity'));
		};
		$container['user'] = function($container) {
			return new \InfamousQ\LManager\Services\UserService($container->get('entity'));
		};
		$container['auth'] = function($container) {
			return new \InfamousQ\LManager\Services\DummyAuthService($container->get('settings')['social']);
		};
		$this->container = $container;
	}

	public function tearDown(){
		self::$T->getRollback("test", "0");
	}

	public function testUpdatingUserReturns200() {
		/** @var User $user */
		$user = $this->container->user->createUserFromArray(['name' => 'Annie Doe', 'email' => 'annie.doe@test.test']);
		/** @var Module $module */
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
		$module_json->name = 'Test module #1';
		$module_json->created = $module->created_at->format('U');
		$module_json->author = new stdClass();
		$module_json->author->id = (int) $user->id;
		$module_json->author->name = $user->name;
		$module_json->author->href = "/api/v1/users/{$user->id}/";
		$this->assertJsonStringEqualsJsonString(json_encode(['id' => (int) $user->id, 'name' => 'Annie Doe', 'href' => "/api/v1/users/{$user->id}/", 'modules' => [$module_json], 'layouts' => []]), (string) $response->getBody());
	}
}