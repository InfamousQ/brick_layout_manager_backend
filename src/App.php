<?php
namespace BLMRA;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class App {
	private $app;

	public function __construct () {
		$settings = require __DIR__ . '/../src/settings.php';
		$app = new \Slim\App($settings);
		// Set up dependencies
		require __DIR__ . '/../src/dependencies.php';
		// Register middleware
		require __DIR__ . '/../src/middleware.php';
		// Register routes
		require __DIR__ . '/../src/routes.php';

		$this->app = $app;
	}

	public function get () {
		return $this->app;
	}
}