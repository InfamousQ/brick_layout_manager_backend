<?php

namespace InfamousQ\LManager\Actions;


use Slim\Http\Request;
use Slim\Http\Response;

class GetHomepageAction extends AbstractAction {

	public function __invoke(Request $request, Response $response) {
		return $this->container->get('view')->render('home::homepage', ['test' => 'Test Data']);
	}

}