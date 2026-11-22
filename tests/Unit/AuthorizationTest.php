<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\RoleMiddleware;
use App\Core\Session;
use App\Exception\ForbiddenException;
use App\Model\Deck;
use App\Model\Enums\Role;
use App\Model\User;
use App\Service\Policies\DeckPolicy;
use PHPUnit\Framework\TestCase;

final class AuthorizationTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->session = new Session(new Config('dev', true, 'http://localhost', 'UTC', 8080));
    }

    public function testUnauthenticatedBrowserGetsRedirect(): void
    {
        $middleware = new AuthMiddleware($this->session);
        $request = new Request('GET', '/profile');

        $response = $middleware->handle($request, fn () => new Response('ok'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->getHeaders()['Location']);
    }

    public function testUnauthenticatedJsonGets401(): void
    {
        $middleware = new AuthMiddleware($this->session);
        $request = new Request('GET', '/api/decks', [], [], ['accept' => 'application/json']);

        $response = $middleware->handle($request, fn () => new Response('ok'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testRoleMiddlewareBlocksNonAdminWith403(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('role', 'USER');

        $middleware = new RoleMiddleware('ADMIN', $this->session);
        $request = new Request('GET', '/admin/dashboard');

        $this->expectException(ForbiddenException::class);
        $middleware->handle($request, fn () => new Response('ok'));
    }

    public function testRoleMiddlewareAllowsAdmin(): void
    {
        $this->session->set('user_id', 2);
        $this->session->set('role', 'ADMIN');

        $middleware = new RoleMiddleware('ADMIN', $this->session);
        $request = new Request('GET', '/admin/dashboard');

        $response = $middleware->handle($request, fn () => new Response('admin-granted'));
        $this->assertSame('admin-granted', $response->getContent());
    }

    public function testDeckPolicyAuthorizesOwnerAndAdmin(): void
    {
        $owner = new User(10, 'owner@example.com', 'h', 'Owner', Role::USER);
        $stranger = new User(20, 'stranger@example.com', 'h', 'Stranger', Role::USER);
        $admin = new User(30, 'admin@example.com', 'h', 'Admin', Role::ADMIN);

        $privateDeck = new Deck(100, 10, 'My Secret Deck', null, false);
        $publicDeck = new Deck(101, 10, 'Public Deck', null, true);

        // Can view
        $this->assertTrue(DeckPolicy::canView($owner, $privateDeck));
        $this->assertFalse(DeckPolicy::canView($stranger, $privateDeck));
        $this->assertTrue(DeckPolicy::canView($stranger, $publicDeck));
        $this->assertTrue(DeckPolicy::canView($admin, $privateDeck));

        // Can edit
        $this->assertTrue(DeckPolicy::canEdit($owner, $privateDeck));
        $this->assertFalse(DeckPolicy::canEdit($stranger, $privateDeck));
        $this->assertTrue(DeckPolicy::canEdit($admin, $privateDeck));

        // Can delete
        $this->assertTrue(DeckPolicy::canDelete($owner, $privateDeck));
        $this->assertFalse(DeckPolicy::canDelete($stranger, $privateDeck));
        $this->assertTrue(DeckPolicy::canDelete($admin, $privateDeck));
    }
}
