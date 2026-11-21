<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controller\DefaultController;
use App\Controller\ProjectController;
use App\Core\Http\Request;
use App\Core\Router;

$request = Request::createFromGlobals();
$router = new Router();

// Rejestracja tras aplikacji
$router->get('/', DefaultController::class, 'index');
$router->get('/projects', ProjectController::class, 'index');
$router->get('/projects/{id}', ProjectController::class, 'show');

$response = $router->dispatch($request);
$response->send();
