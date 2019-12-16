<?php

use \InfamousQ\LManager\Actions\APIColorAction;
use \InfamousQ\Lmanager\Models\Color;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class APIColorTest extends \PHPUnit\Framework\TestCase {


	/** @var \Phinx\Wrapper\TextWrapper $T */
	protected static $T;
	/** @var \Slim\Container $container */
	protected $container;

	protected function setUp(): void {
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);
		$app->run(new \Symfony\Component\Console\Input\StringInput(' '), new \Symfony\Component\Console\Output\NullOutput());

		self::$T = new \Phinx\Wrapper\TextWrapper($app, array('configuration' => '.deploy/php/phinx.php'));
		self::$T->getMigrate("test");

		$container = new \Slim\Container();
		$container['settings'] = [
			'db' => [
				'host' => getenv('DB_HOST'),
				'port' => getenv('DB_PORT'),
				'dbname' => getenv('DB_NAME'),
				'user' => getenv('DB_USER'),
				'password' => getenv('DB_PASS'),
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
		$this->container = $container;
		return;
	}

	public function tearDown(): void {
		self::$T->getRollback("test", "0");
		$this->container->entity->closeConnectionToDB();
	}

	public function testFetchColorListReturns200() {
		/** @var Color $red_color */
		$red_color = $this->container->module->createColor('Red', 'FF0000');
		/** @var Color $blue_color */
		$blue_color = $this->container->module->createColor('Blue', '0000FF');

		$action = new APIColorAction($this->container);
		$env = Environment::mock([
			'REQUEST_METHOD'    => 'GET',
			'REQUEST_URI'       => '/api/v1/colors/',
		]);
		$request = Request::createFromEnvironment($env);
		$response = new Response();

		$response = $action->fetchList($request, $response);
		$this->assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
		$red_color_json = new stdClass();
		$red_color_json->id = (int) $red_color->id;
		$red_color_json->name = $red_color->name;
		$red_color_json->hex = $red_color->hex;
		$blue_color_json = new stdClass();
		$blue_color_json->id = (int) $blue_color->id;
		$blue_color_json->name = $blue_color->name;
		$blue_color_json->hex = $blue_color->hex;
		$this->assertJsonStringEqualsJsonString( json_encode([$red_color_json, $blue_color_json]), (string) $response->getBody(), 'Color list is correct');
	}
}