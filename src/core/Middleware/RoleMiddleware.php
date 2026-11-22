<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\JsonResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Session;
use App\Exception\ForbiddenException;

final class RoleMiddleware implements MiddlewareInterface
{
    private string $requiredRole;
    private ?Session $session;

    public function __construct(string $requiredRole = 'ADMIN', ?Session $session = null)
    {
        $this->requiredRole = strtoupper($requiredRole);
        $this->session = $session;
    }

    public function handle(Request $request, callable $next): Response
    {
        $userRole = null;

        if ($this->session !== null) {
            $userRole = (string) $this->session->get('role', '');
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            $userRole = (string) ($_SESSION['role'] ?? '');
        }

        if ($userRole === '') {
            if ($request->expectsJson() || $request->isJson()) {
                return new JsonResponse(['success' => false, 'error' => 'Unauthorized.'], 401);
            }
            return new \App\Core\Http\RedirectResponse('/login', 302);
        }

        if (strtoupper($userRole) !== $this->requiredRole) {
            if ($request->expectsJson() || $request->isJson()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => "Forbidden. Required role: {$this->requiredRole}",
                ], 403);
            }

            throw new ForbiddenException("Brak wymaganych uprawnień roli {$this->requiredRole}.");
        }

        return $next($request);
    }
}
