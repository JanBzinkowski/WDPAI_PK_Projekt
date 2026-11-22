<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\AuthController;
use App\Core\Config;
use App\Core\CsrfToken;
use App\Core\Http\Request;
use App\Core\Session;
use PHPUnit\Framework\TestCase;

final class LogoutTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->session = new Session(new Config('dev', true, 'http://localhost', 'UTC', 8080));
        $this->session->set('user_id', 42);
        $this->session->set('username', 'testuser');
    }

    public function testCsrfTokenGenerationAndValidation(): void
    {
        $token = CsrfToken::generate($this->session);
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));

        $this->assertTrue(CsrfToken::validate($this->session, $token));
        $this->assertFalse(CsrfToken::validate($this->session, 'invalid-token'));
    }

    public function testLogoutClearsSessionAndRedirects(): void
    {
        $this->assertTrue($this->session->has('user_id'));

        $authController = new AuthController(null, null, $this->session);
        $request = new Request('POST', '/logout');

        $response = $authController->logout($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?logged_out=1', $response->getHeaders()['Location']);
        $this->assertEmpty($_SESSION);
    }

    public function testJsonLogoutResponse(): void
    {
        $authController = new AuthController(null, null, $this->session);
        $request = new Request('POST', '/logout', [], [], ['accept' => 'application/json']);

        $response = $authController->logout($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaders()['Content-Type']);

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }
}
