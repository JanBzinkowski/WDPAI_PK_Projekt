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

    public function showLogin(): Response
    {
        return $this->render('auth/login', [
            'pageTitle' => 'Logowanie - Flashcards Leitner',
        ]);
    }

    public function login(Request $request): Response
    {
        $email = (string) $request->getBody('email', '');
        $password = (string) $request->getBody('password', '');
        $ip = (string) ($request->getServer('REMOTE_ADDR') ?? '127.0.0.1');

        $user = $this->authService->authenticate($email, $password, $ip);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user->getRole()->value;

        if ($request->expectsJson() || $request->isJson()) {
            return $this->json([
                'success' => true,
                'message' => 'Zalogowano pomyślnie.',
                'user' => $user->toArray(),
            ]);
        }

        $returnUrl = (string) $request->getQuery('return_url', '/');
        return $this->redirect($returnUrl !== '' ? $returnUrl : '/');
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
