<?php

namespace InfamousQ\LManager;

use \Hybridauth\Adapter\AdapterInterface;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\InvalidAuthorizationStateException;
use Hybridauth\Storage\Session;
use Hybridauth\User\Profile;
use InfamousQ\LManager\Middleware\JWTAuthenticationMiddleware;
use InfamousQ\LManager\Services\UserHandler;
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;
use \League\Plates\Engine as Renderer;
use \Noodlehaus\Config as ConfigReader;
use \Hybridauth\Hybridauth as AuthService;

use Firebase\JWT\JWT;
use Tuupola\Base62;
use Tuupola\Middleware\JwtAuthentication;


/**
 * @property $auth AuthService Current instance of HybridAuth auth service
 * @property $authconfig ConfigReader Configuration for HybridAuth service
 * @property $view Renderer Current Plates renderer
 */
class App {
    /** @var $app \Slim\App Current instance of the Slim application */
    private $app;

    public function __construct() {
        $app_config = $this->readConfig();
        $this->app = new \Slim\App($app_config);

        // Set DI components
        $container = $this->app->getContainer();

        // Register Plates renderer
        $container['view'] = function ($container) {
            $base_folder = $container->get('installation_folder');
            $renderer = new Renderer($base_folder . 'src/Templates');
            // Add template folders
            $renderer->addFolder('home', $base_folder . 'src/Templates/home');
            $renderer->addFolder('user', $base_folder . 'src/Templates/user');
            $renderer->addFolder('layout', $base_folder . 'src/Templates/layouts');
            return $renderer;
        };

        // Register configuration provider for HybridAuth
        $container['authconfig'] = function () {
            return new \DavidePastore\Slim\Config\Config(__DIR__.'/../config/social.json');
        };
        $this->app->add($container->get('authconfig'));

        // Register HybridAuth
        $container['auth'] = function ($container) {
            return new AuthService($container->authconfig->getConfig()->all());
        };

        // Home page
        $this->app->get('/', function (Request $request, Response $response) {
            return $this->view->render('home::homepage', ['test' => 'Test Data']);
        });

        // User login
        $this->app->get('/user', function (Request $request, Response $response, $args) {
        	$connected_providers = $this->auth->getConnectedProviders();
        	/** @var AdapterInterface $active_adapter */
        	$active_adapter = null;
        	/** @var Profile $user_profile */
        	$user_profile = null;
        	/** @var int $user_id */
        	$user_id = null;
        	if (count($connected_providers) > 0) {
        		$active_adapter = $this->auth->getAdapter($connected_providers[0]);
        		$user_profile = $active_adapter->getUserProfile();
        		$user_id = UserHandler::findUserIdByEmail($user_profile->email);
	        }
        	// TODO: Should active adapter work as middleware?
            return $this->view->render('user::login', ['profile' => $user_profile, 'user_id' => $user_id]);
        })->setName('user_profile');

        // 3rd party social service login
        $this->app->get('/user/authenticate', function (Request $request, Response $response) {
	        /** @var string $param_provider Name of provider to authenticate with */
	        $param_provider = $request->getQueryParam('provider', '');
	        /** @var int $param_error_code Error code from Hybridauth provider service */
	        $param_error_code = $request->getQueryParam('error_code', 0);
	        /** @var string $param_error_msg Error message from Hybridauth provider service */
	        $param_error_msg = $request->getQueryParam('error_message', '');

	        $storage = new Session();
	        if (!empty($param_provider)) {
	        	$storage->set('provider', $param_provider);
            }

            if ($param_error_code > 0) {
                // TODO: Add proper template
                echo "Authentication error: $param_error_code <br><br> $param_error_msg <br><br> <a href='/user/authenticate'>Try again</a>";
                exit();
            }

            try {
            	if ($provider = $storage->get('provider')) {
		            /** @var AdapterInterface $adapter */
		            $adapter = $this->auth->authenticate($provider);
		            if ($adapter->isConnected()) {
		            	// Check if user exists and create if not
			            /** @var Profile $profile */
			            $profile = $adapter->getUserProfile();
			            $existing_user_id = UserHandler::findUserIdByEmail($profile->email);
			            if (false == $existing_user_id) {
			            	$new_user_id = UserHandler::createUserForProfile($profile);
			            	if (false == $new_user_id) {
			            		// TODO: handle failure
					            error_log('Could not generate new user for');
				            }
			            }
			            $storage->set('provider', null);
			            return $response->withRedirect('/user');
		            }
	            }
            	// TODO: Throw error
	            echo "Authentication error: Provider not saved to session correctly".PHP_EOL;
            } catch (InvalidArgumentException $e) {
            	// TODO: Add error logging
            	echo "Authentication error: Provider $param_provider is not configured".PHP_EOL;
            } catch (InvalidAuthorizationStateException $e) {
            	return $response->withRedirect('/');
            }
            return $response->withRedirect('/');

        })->setName('auth_callback');

        $this->app->get('/user/logout', function (Request $request, Response $response) {
            $this->auth->disconnectAllAdapters();
            return $response->withRedirect('/');
        })->setName('auth_logout');

        // API auth
        $this->app->get('/user/token', function (Request $request, Response $response, $args) {
            return $response->withJson(['token' => 'not_implemented']);
        })->setName('user_token');

        // API resources
        $this->app->group('/api', function () {

            $this->map(['GET'], '', function (Request $request, Response $response) {
                return $response->withJson(['message' => 'API GET']);
            });

            $this->get('/{id}', function (Request $request, Response $response, $args) {
                return $response->withJson(['message' => 'API GET BY ID ' . $args['id']]);
            });

            $this->map(['POST'], '/{id}', function (Request $request, Response $response, $args) {
                return $response->withJson(['message' => 'API POST BY ID ' . $args['id']]);
            });


            $this->map(['DELETE'], '/{id}', function (Request $request, Response $response, $args) {
                return $response->withJson(['message' => 'API DELETE BY ID ' . $args['id']]);
            });

        })->add(new JWTAuthenticationMiddleware($this->app->getContainer()));
    }

    protected function readConfig() {
        $common_config_reader = ConfigReader::load(__DIR__.'/../config/common.json');
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