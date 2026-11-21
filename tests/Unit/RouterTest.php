<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\AppController;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\MiddlewareInterface;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class DummyController extends AppController
{
    public function home(): Response
    {
        return new Response('home-page');
    }

    public function show(int $id): Response
    {
        return new Response('project-' . $id);
    }

    public function deleteItem(int $id): Response
    {
        return new Response('deleted-' . $id);
    }
}

class TestHeaderMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        return $response->withHeader('X-Middleware', 'Executed');
    }
}

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->get('/', DummyController::class, 'home');
        $this->router->get('/projects/{id}', DummyController::class, 'show', [new TestHeaderMiddleware()]);
        $this->router->delete('/projects/{id}', DummyController::class, 'deleteItem');
    }

    public function testStaticRouteMatches(): void
    {
        $request = new Request('GET', '/');
        $response = $this->router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('home-page', $response->getContent());
    }

    public function testParameterizedRouteMatchesWithMiddleware(): void
    {
        $request = new Request('GET', '/projects/42');
        $response = $this->router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('project-42', $response->getContent());
        $this->assertSame('Executed', $response->getHeaders()['X-Middleware'] ?? null);
    }

    public function testMethodNotAllowedReturns405(): void
    {
        $request = new Request('POST', '/');
        $response = $this->router->dispatch($request);

        $this->assertSame(405, $response->getStatusCode());
        $this->assertArrayHasKey('Allow', $response->getHeaders());
        $this->assertStringContainsString('GET', $response->getHeaders()['Allow']);
    }

    public function testNonExistentRouteReturns404(): void
    {
        $request = new Request('GET', '/non-existent-path');
        $response = $this->router->dispatch($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testHttpMethodOverride(): void
    {
        $request = new Request(
            'POST',
            '/projects/99',
            [],
            ['_method' => 'DELETE']
        );

        $fromGlobalsRequest = new Request('DELETE', '/projects/99');
        $response = $this->router->dispatch($fromGlobalsRequest);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('deleted-99', $response->getContent());
    }
}
