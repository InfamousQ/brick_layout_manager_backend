<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/modules/{id}', \App\ModuleController::class . ':view_single');

$app->post('/modules/{id}', \App\ModuleController::class . ':edit_single');