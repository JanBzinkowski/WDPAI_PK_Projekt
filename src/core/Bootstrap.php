<?php

declare(strict_types=1);

namespace App\Core;

use App\Controller\DefaultController;
use App\Controller\ProjectController;
use App\Core\Http\Request;
use App\Core\Http\Response;

final class Bootstrap
{
    private Container $container;
    private Router $router;

    public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public static function boot(): self
    {
        $container = new Container();

        // Rejestracja konfiguracji i podstawowych usług
        $config = Config::fromEnv();
        $container->instance(Config::class, $config);
        $container->instance(Container::class, $container);

        $router = new Router($container);
        $container->instance(Router::class, $router);

        $app = new self($container, $router);
        $app->registerRoutes();

        return $app;
    }

    public function registerRoutes(): void
    {
        $this->router->get('/', DefaultController::class, 'index');
        $this->router->get('/projects', ProjectController::class, 'index');
        $this->router->get('/projects/{id}', ProjectController::class, 'show');
    }

    public function handle(Request $request): Response
    {
        return $this->router->dispatch($request);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
