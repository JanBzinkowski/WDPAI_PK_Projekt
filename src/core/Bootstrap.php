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

        $session = new Session($config);
        $container->instance(Session::class, $session);
        $session->start();

        $database = new Database($config);
        $container->instance(Database::class, $database);

        $translator = new Translator('pl');
        $container->instance(Translator::class, $translator);

        $userRepository = new \App\Repository\PgUserRepository($database);
        $container->instance(\App\Repository\UserRepositoryInterface::class, $userRepository);
        $container->instance(\App\Repository\PgUserRepository::class, $userRepository);

        $authService = new \App\Service\AuthService($userRepository, $translator);
        $container->instance(\App\Service\AuthService::class, $authService);

        $errorHandler = new ErrorHandler($config);
        $errorHandler->register();
        $container->instance(ErrorHandler::class, $errorHandler);

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

        // Autentykacja
        $this->router->get('/login', \App\Controller\AuthController::class, 'showLogin');
        $this->router->post('/login', \App\Controller\AuthController::class, 'login');
        $this->router->post('/logout', \App\Controller\AuthController::class, 'logout');
        $this->router->get('/logout', \App\Controller\AuthController::class, 'logout');
        $this->router->get('/register', \App\Controller\AuthController::class, 'showRegister');
        $this->router->post('/register', \App\Controller\AuthController::class, 'register');
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (\Throwable $e) {
            /** @var ErrorHandler $errorHandler */
            $errorHandler = $this->container->get(ErrorHandler::class);
            return $errorHandler->handle($e, $request);
        }
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
