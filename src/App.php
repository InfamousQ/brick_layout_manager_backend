<?php

namespace InfamousQ\LManager;

use Firebase\JWT\JWT;
use InfamousQ\LManager\Actions\APIColorAction;
use InfamousQ\LManager\Actions\APILayoutAction;
use InfamousQ\LManager\Actions\APIModuleAction;
use InfamousQ\LManager\Actions\GetHomepageAction;
use InfamousQ\LManager\Actions\GetPingAction;
use InfamousQ\LManager\Actions\GetUserAuthenticateAction;
use InfamousQ\LManager\Actions\GetUserLogoutAction;
use InfamousQ\LManager\Actions\GetUserTokenAction;
use InfamousQ\LManager\Actions\APIUserAction;
use InfamousQ\LManager\Services\EntityMapperService;
use InfamousQ\LManager\Services\HybridAuthService;
use InfamousQ\LManager\Services\JWTService;
use InfamousQ\LManager\Services\ModuleService;
use Noodlehaus\Config;
use Slim\Container;
use \League\Plates\Engine as Renderer;
use Slim\Http\Response;


/**
 * @property $auth AuthService Current instance of HybridAuth auth service
 * @property $authconfig ConfigReader Configuration for HybridAuth service
 * @property $view Renderer Current Plates renderer
 */
class App {

	/** @var $app \Slim\App Current instance of the Slim application */
	private $app;

	public function __construct() {
		JWT::$leeway = 10;
		error_reporting(-1);
		ini_set('display_errors', 1);
		$app_config = self::readConfig();
		$this->app = new \Slim\App($app_config);

		$this->setDepencyInjectionComponents();
		$this->setRoutes();
	}

	protected function setDepencyInjectionComponents() {
		// Set DI components
		$container = $this->app->getContainer();

		// Register ORM connection
		$container['entity'] = function (Container $container) {
			return new EntityMapperService($container->get('settings')['db']);
		};

		// Register module service
		$container['module'] = function (Container $container) {
			return new ModuleService($container->get('entity'));
		};

		// Register user service
		$container['user'] = function (Container $container) {
			return new Services\UserService($container->get('entity'));
		};

		// Register Plates renderer
		$container['view'] = function (Container $container) {
			$base_folder = $container->get('installation_folder');
			$renderer = new Renderer($base_folder . 'src/Templates');
			// Add template folders
			$renderer->addFolder('home', $base_folder . 'src/Templates/home');
			$renderer->addFolder('user', $base_folder . 'src/Templates/user');
			$renderer->addFolder('layout', $base_folder . 'src/Templates/layouts');
			return $renderer;
		};

		// Register authentication service. Use HybridauthService
		$container['auth'] = function (Container $container) {
			return new HybridAuthService($container->get('settings')['social']);
		};

		// Register token service. Use JWTService
		$container['jwt'] = function (Container $container) {
			return new JWTService($container->get('settings')['jwt'], $container->get('user'));
		};
	}

	protected function setRoutes() {
		// Home page
		$this->app->get('/', GetHomepageAction::class);

		// User login
		$this->app->get('/user/authenticate', GetUserAuthenticateAction::class);
		// User token
		$this->app->get('/user/token', GetUserTokenAction::class);
		// User logout
		$this->app->get('/user/logout', GetUserLogoutAction::class);

		// API resources
		$this->app->group('/api/v1', function () {

			$this->get('/ping', GetPingAction::class);

			$this->group('/user', function () {
				$this->get('/providers', APIUserAction::class.':providers');
				$this->get('/[{id}]', APIUserAction::class.':fetch');
				$this->post('/{id}', APIUserAction::class.':update');
			});

			$this->group('/modules', function () {
				$this->get('/', APIModuleAction::class.':fetchList');
				$this->post('/', APIModuleAction::class.':insert');
				$this->get('/{id}', APIModuleAction::class.':fetchSingle');
				$this->put('/{id}', APIModuleAction::class.':editSingle');
				$this->get('/{id}/plates', APIModuleAction::class.':fetchPlateList');
				$this->post('/{id}/plates', APIModuleAction::class.':insertPlate');
				$this->put('/{id}/plates/{plate_id}', APIModuleAction::class.':editPlate');
				$this->delete('/{id}/plates/{plate_id}', APIModuleAction::class.':deletePlate');
			});

			$this->group('/layouts', function () {
				$this->get('/', APILayoutAction::class.':fetchList');
				$this->post('/', APILayoutAction::class.':insert');
				$this->get('/{id}', APILayoutAction::class.':fetchSingle');
				$this->put('/{id}', APILayoutAction::class.':editSingle');
				$this->post('/{id}/modules', APILayoutAction::class.':addModuleToLayout');
				$this->put('/{id}/modules/{module_id}', APILayoutAction::class.':editModuleInLayout');
				$this->delete('/{id}/modules/{module_id}', APILayoutAction::class.':deleteModuleInLayout');
			});

			$this->get('/colors', APIColorAction::class.':fetchList');

		})->add(new \Tuupola\Middleware\JwtAuthentication([
			'secret' => $this->app->getContainer()->get('settings')['jwt']['key'],
			'path' => ['/api/v1'],
			'ignore' => ['/api/v1/ping', '/api/v1/user/providers', '/api/v1/colors'],
			"error" => function (Response $response, $arguments) {
				$data["status"] = "error";
				$data["message"] = $arguments["message"];
				return $response
					->withHeader("Content-Type", "application/json")
					->withJson($data);
			}
		]));
	}

	protected static function readConfig() {
		$common_config_reader = new Config([
			__DIR__ . '/../config/common.json',
			'?'.__DIR__.'/../config/social.json',
			'?'.__DIR__.'/../config/db.json',
			'?'.__DIR__.'/../config/jwt.json',
		]);
		return $common_config_reader->all();
	}

	/**
	 * Get current instance of the Slim application
	 * @return \Slim\App
	 */
	public function getSlim() {
		return $this->app;
	}
}