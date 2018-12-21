<?php

namespace InfamousQ\LManager;

use InfamousQ\LManager\Actions\GetHomepageAction;
use InfamousQ\LManager\Actions\GetPingAction;
use InfamousQ\LManager\Actions\GetUserAction;
use InfamousQ\LManager\Actions\GetUserAuthenticateAction;
use InfamousQ\LManager\Actions\GetUserLogoutAction;
use InfamousQ\LManager\Actions\GetUserTokenAction;
use InfamousQ\LManager\Services\HybridAuthService;
use InfamousQ\LManager\Services\PDODatabaseService;
use Noodlehaus\Config;
use Slim\Container;
use \League\Plates\Engine as Renderer;


/**
 * @property $auth AuthService Current instance of HybridAuth auth service
 * @property $authconfig ConfigReader Configuration for HybridAuth service
 * @property $view Renderer Current Plates renderer
 */
class App {
	const MODE_TEST = 1;
	const MODE_PROD = 2;

	/** @var $app \Slim\App Current instance of the Slim application */
	private $app;

	public function __construct() {
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

		// Register DB connection
		$container['db'] = function (Container $container) {
			return new PDODatabaseService($container->get('settings')['db']);
		};

		// Register user service
		$container['user'] = function (Container $container) {
			return new Services\UserService($container->get('db'));
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
	}

	protected function setRoutes() {
		// Home page
		$this->app->get('/', GetHomepageAction::class);

		// User login
		$this->app->get('/user',GetUserAction::class);

		$this->app->get('/user/logout', GetUserLogoutAction::class);

		// API auth
		$this->app->get('/user/token', GetUserTokenAction::class);

		// API resources
		$this->app->group('/api/v1', function () {

			$this->get('/ping', GetPingAction::class);

			$this->group('/user', function () {

				$this->get('/authenticate', GetUserAuthenticateAction::class);
			});

		});
	}

	protected static function readConfig() {
		$common_config_reader = new Config([
			__DIR__ . '/../config/common.json',
			'?'.__DIR__.'/../config/social.json',
			'?'.__DIR__.'/../config/db.json',
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