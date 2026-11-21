<?php

declare(strict_types=1);

namespace App\Core;

use App\Controller\AppController;
use App\Controller\DefaultController;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\MiddlewareInterface;

class Router
{
    protected ?Container $container;

    /** @var array<int, array{method: string, path: string, pattern: string, controller: class-string, action: string, middlewares: array<class-string|MiddlewareInterface>}> */
    protected array $routes = [];

    public function __construct(?Container $container = null)
    {
        $this->container = $container;
    }

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function get(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->add('GET', $path, $controller, $action, $middlewares);
    }

    public function post(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->add('POST', $path, $controller, $action, $middlewares);
    }

    public function put(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->add('PUT', $path, $controller, $action, $middlewares);
    }

    public function patch(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->add('PATCH', $path, $controller, $action, $middlewares);
    }

    public function delete(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $controller, $action, $middlewares);
    }

    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        array $middlewares = []
    ): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $this->compilePattern($path),
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    protected function compilePattern(string $path): string
    {
        $parts = explode('/', $path);

        foreach ($parts as &$part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $trimmed = trim($part, '{}');
                if (str_contains($trimmed, ':')) {
                    [$name, $regex] = explode(':', $trimmed, 2);
                    $part = '(?P<' . $name . '>' . $regex . ')';
                } else {
                    $part = '(?P<' . $trimmed . '>\d+)';
                }
                continue;
            }

            $part = preg_quote($part, '~');
        }
        unset($part);

        return '~^' . implode('/', $parts) . '$~';
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        $matchedRoutes = [];
        $methodsAllowed = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $methodsAllowed[] = $route['method'];
                if ($route['method'] === $method) {
                    $matchedRoutes[] = [
                        'route' => $route,
                        'matches' => $matches,
                    ];
                }
            }
        }

        if (empty($matchedRoutes)) {
            if (!empty($methodsAllowed)) {
                return new Response(
                    'Method Not Allowed',
                    405,
                    ['Allow' => implode(', ', array_unique($methodsAllowed))]
                );
            }

            return (new DefaultController())->notFound();
        }

        $selected = $matchedRoutes[0];
        $route = $selected['route'];
        $matches = $selected['matches'];

        $parameters = array_filter(
            $matches,
            static fn (int|string $key): bool => is_string($key),
            ARRAY_FILTER_USE_KEY,
        );

        $parameters = array_map(
            static fn (string $value): int|string =>
                ctype_digit($value) ? (int) $value : $value,
            $parameters,
        );

        $controllerClass = $route['controller'];
        $action = $route['action'];

        $coreAction = function (Request $req) use ($controllerClass, $action, $parameters): Response {
            $controller = $this->createController($controllerClass);

            $reflection = new \ReflectionMethod($controller, $action);
            $reflectionParams = $reflection->getParameters();
            $args = [];

            foreach ($reflectionParams as $p) {
                $pName = $p->getName();
                $pType = $p->getType();

                if ($pType instanceof \ReflectionNamedType && $pType->getName() === Request::class) {
                    $args[] = $req;
                } elseif (array_key_exists($pName, $parameters)) {
                    $args[] = $parameters[$pName];
                } elseif ($p->isDefaultValueAvailable()) {
                    $args[] = $p->getDefaultValue();
                } else {
                    $args[] = array_shift($parameters);
                }
            }

            $result = $controller->{$action}(...$args);

            if ($result instanceof Response) {
                return $result;
            }

            return new Response((string) $result);
        };

        return $this->runMiddlewares($route['middlewares'], $request, $coreAction);
    }

    protected function createController(string $controllerClass): AppController
    {
        if ($this->container !== null && $this->container->has($controllerClass)) {
            $controller = $this->container->get($controllerClass);
        } else {
            $controller = new $controllerClass();
        }

        if (!$controller instanceof AppController) {
            throw new \RuntimeException('Invalid controller: ' . $controllerClass);
        }

        return $controller;
    }

    /**
     * @param array<class-string|MiddlewareInterface> $middlewares
     */
    protected function runMiddlewares(array $middlewares, Request $request, callable $target): Response
    {
        $pipeline = array_reduce(
            array_reverse($middlewares),
            function (callable $next, mixed $middleware) {
                return function (Request $req) use ($middleware, $next): Response {
                    $instance = is_string($middleware) ? new $middleware() : $middleware;
                    if (!$instance instanceof MiddlewareInterface) {
                        throw new \RuntimeException('Invalid middleware');
                    }
                    return $instance->handle($req, $next);
                };
            },
            $target
        );

        return $pipeline($request);
    }
}
