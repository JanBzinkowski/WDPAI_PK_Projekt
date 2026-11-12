<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controller\DefaultController;
use App\Controller\ProjectController;

$routing = new Routing();

// TU BĘDZIEMY DEFINIOWAĆ WSZYSTKIE TRASY WYSTĘPUJĄCE W NASZYM PROJEKCIE
$routing->get('/projects', ProjectController::class, 'index');
$routing->get('/projects/{id}', ProjectController::class, 'show');
$routing->get('/', DefaultController::class, 'index');

$routeFound = $routing->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
);

if (!$routeFound) {
    http_response_code(404);
    (new DefaultController())->notFound();
}
