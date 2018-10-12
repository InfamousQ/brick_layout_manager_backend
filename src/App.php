<?php

namespace InfamousQ\LManager;

use InfamousQ\LManager\Middleware\JWTAuthenticationMiddleware;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \League\Plates\Engine as Renderer;

use Firebase\JWT\JWT;
use Tuupola\Base62;
use Tuupola\Middleware\JwtAuthentication;

class App {

    /** @var  \Slim\App Current instance of the Slim application */
    private $app;

    public function __construct() {
        $this->app = new \Slim\App(['settings' => ['debug' => true], 'displayErrorDetails' => true]);

        // Set DI components
        $container = $this->app->getContainer();
        $container['view'] = function ($container) {
            $renderer = new Renderer("/var/www/lmanager/src/Templates");
            // Add template folders
            $renderer->addFolder('home', '/var/www/lmanager/src/Templates/home');
            $renderer->addFolder('user', '/var/www/lmanager/src/Templates/user');
            $renderer->addFolder('layout', '/var/www/lmanager/src/Templates/layouts');
            return $renderer;
        };

        // Home page
        $this->app->get('/', function (Request $request, Response $response) {
            return $this->view->render('home::homepage', ['test' => 'Test Data']);
        });

        // User login
        $this->app->get('/user', function (Request $request, Response $response, $args) {
            return $this->view->render('user::login');
        });

        // API auth
        $this->app->get('/user/token', function (Request $request, Response $response, $args) {
                return $response->withJson(['token' => 'not_implemented']);
            })->setName('user_token');

        // API resources
        $this->app->group('/api', function() {

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

    /**
     * Get current instance of the Slim application
     * @return \Slim\App
     */
    public function getSlim() {
        return $this->app;
    }
}