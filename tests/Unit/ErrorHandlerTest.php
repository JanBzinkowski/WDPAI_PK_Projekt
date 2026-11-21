<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\ErrorHandler;
use App\Core\Http\Request;
use App\Exception\ConflictException;
use App\Exception\DomainException;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $config = new Config('dev', true, 'http://localhost:8080', 'UTC', 8080);
        $this->handler = new ErrorHandler($config);
    }

    public function testStatusCodesMapping(): void
    {
        $this->assertSame(400, $this->handler->resolveStatusCode(new ValidationException(['email' => 'Invalid email'])));
        $this->assertSame(401, $this->handler->resolveStatusCode(new UnauthorizedException()));
        $this->assertSame(403, $this->handler->resolveStatusCode(new ForbiddenException()));
        $this->assertSame(404, $this->handler->resolveStatusCode(new NotFoundException()));
        $this->assertSame(409, $this->handler->resolveStatusCode(new ConflictException()));
        $this->assertSame(422, $this->handler->resolveStatusCode(new DomainException()));
        $this->assertSame(500, $this->handler->resolveStatusCode(new \RuntimeException('Unknown')));
    }

    public function testJsonResponseOnAcceptHeader(): void
    {
        $request = new Request('GET', '/api/resource', [], [], ['accept' => 'application/json']);
        $exception = new ValidationException(['title' => 'Required field'], 'Validation failed');

        $response = $this->handler->handle($exception, $request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaders()['Content-Type']);

        $decoded = json_decode($response->getContent(), true);
        $this->assertFalse($decoded['success']);
        $this->assertSame(400, $decoded['status']);
        $this->assertSame('Validation failed', $decoded['message']);
        $this->assertSame('Required field', $decoded['errors']['title']);
    }

    public function testHtmlResponseForBrowser(): void
    {
        $request = new Request('GET', '/profile');
        $exception = new ForbiddenException('Brak dostępu do profilu');

        $response = $this->handler->handle($exception, $request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaders()['Content-Type']);
        $this->assertStringContainsString('403', $response->getContent());
        $this->assertStringContainsString('Brak dostępu', $response->getContent());
    }
}
