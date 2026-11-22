<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View;
use App\Service\AuthService;

final class AuthController extends AppController
{
    private AuthService $authService;

    public function __construct(?View $view = null, ?AuthService $authService = null)
    {
        parent::__construct($view);
        $this->authService = $authService ?? new AuthService(
            new \App\Repository\PgUserRepository(new \App\Core\Database(\App\Core\Config::fromEnv()))
        );
    }

    public function showRegister(): Response
    {
        return $this->render('auth/register', [
            'pageTitle' => 'Rejestracja - Flashcards Leitner',
        ]);
    }

    public function register(Request $request): Response
    {
        $body = $request->getBody();
        $user = $this->authService->register($body);

        if ($request->expectsJson() || $request->isJson()) {
            return $this->json([
                'success' => true,
                'message' => 'Rejestracja zakończona sukcesem.',
                'user' => $user->toArray(),
            ], 201);
        }

        return $this->redirect('/login?registered=1');
    }
}
