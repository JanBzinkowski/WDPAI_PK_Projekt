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

    public function __construct(
        ?View $view = null,
        ?AuthService $authService = null,
        ?\App\Core\Session $session = null
    ) {
        parent::__construct($view, $session);
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

        if ($this->session !== null) {
            $this->session->regenerate(true);
            $this->session->set('user_id', $user->getId());
            $this->session->set('user', $user);
            $this->session->set('role', $user->getRole()->value);
        } else {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $user->getRole()->value;
        }

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

    public function logout(Request $request): Response
    {
        if ($this->session !== null) {
            $this->session->destroy();
        } else {
            $_SESSION = [];
            if (ini_get('session.use_cookies') && !headers_sent()) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
        }

        if ($request->expectsJson() || $request->isJson()) {
            return $this->json([
                'success' => true,
                'message' => 'Wylogowano pomyślnie.',
            ]);
        }

        return $this->redirect('/login?logged_out=1');
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
