<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Http\JsonResponse;
use App\Core\Http\RedirectResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use PHPUnit\Framework\TestCase;

final class HttpTest extends TestCase
{
    public function testRequestProperties(): void
    {
        $request = new Request(
            'POST',
            '/api/test?filter=active',
            ['filter' => 'active'],
            ['name' => 'John'],
            ['content-type' => 'application/json', 'accept' => 'application/json']
        );

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/test', $request->getPath());
        $this->assertSame('active', $request->getQuery('filter'));
        $this->assertSame('John', $request->getBody('name'));
        $this->assertTrue($request->isJson());
        $this->assertTrue($request->expectsJson());
    }

    public function testResponseCreation(): void
    {
        $response = new Response('Hello World', 200, ['X-Custom' => 'Value']);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello World', $response->getContent());
        $this->assertSame('Value', $response->getHeaders()['X-Custom']);

        $modified = $response->withStatus(201)->withHeader('X-New', '123');
        $this->assertSame(201, $modified->getStatusCode());
        $this->assertSame('123', $modified->getHeaders()['X-New']);
    }

    public function testJsonResponse(): void
    {
        $data = ['success' => true, 'id' => 42];
        $jsonResponse = new JsonResponse($data, 201);

        $this->assertSame(201, $jsonResponse->getStatusCode());
        $this->assertStringContainsString('application/json', $jsonResponse->getHeaders()['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode($data), $jsonResponse->getContent());
    }

    public function testRedirectResponse(): void
    {
        $redirect = new RedirectResponse('/login', 302);
        $this->assertSame(302, $redirect->getStatusCode());
        $this->assertSame('/login', $redirect->getHeaders()['Location']);
    }
}
