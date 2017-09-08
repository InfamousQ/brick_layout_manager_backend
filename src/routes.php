<?php
// Routes

// "Home page"
$app->get('/[{name}]', function (Request $request, Response $response, $args) {
	$response->getBody()->write('Index');
	return $response;
});

// Module
$app->get('/modules/{id}', BLMRA\Controller\ModuleController::class . ':view_single');

$app->post('/modules/{id}', BLMRA\Controller\ModuleController::class . ':edit_single');