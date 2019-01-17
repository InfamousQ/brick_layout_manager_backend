<?php

namespace InfamousQ\LManager\Actions;

use Slim\Http\Request;
use Slim\Http\Response;

class GetHomepageAction {
	/** @var \League\Plates\Engine; $container */
	protected $view;
	// TODO: Create own User model
	protected $current_profile;

	public function __construct(\Slim\Container $container) {
		$this->view = $container->get('view');
		$this->current_profile = (object) [
			'displayName' => 'Test user',
			'email' => 'test.test@test.test',
			'id' => 123,
		];
	}

	public function __invoke(Request $request, Response $response) {
		$this->current_profile = null;
		return $this->view->render('home::homepage', ['test' => 'Test Data', 'profile' => $this->current_profile]);
	}

}