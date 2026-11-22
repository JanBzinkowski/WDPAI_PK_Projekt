<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\JsonResponse;
use App\Core\Http\RedirectResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Session;

final class AuthMiddleware implements MiddlewareInterface
{
    private ?Session $session;

    public function __construct(?Session $session = null)
    {
        $this->session = $session;
    }

    public function handle(Request $request, callable $next): Response
    {
        $isLoggedIn = false;

        if ($this->session !== null) {
            $isLoggedIn = $this->session->has('user_id');
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            $isLoggedIn = isset($_SESSION['user_id']);
        }

        if (!$isLoggedIn) {
            if ($request->expectsJson() || $request->isJson()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                ], 401);
            }

            $returnUrl = urlencode($request->getUri());
            return new RedirectResponse('/login?return_url=' . $returnUrl, 302);
        }

        return $next($request);
    }
}
