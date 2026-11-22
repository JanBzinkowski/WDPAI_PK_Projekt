<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Http\JsonResponse;
use App\Core\Http\RedirectResponse;
use App\Core\Http\Response;
use App\Core\View;

require_once dirname(__DIR__) . '/core/helpers.php';

abstract class AppController
{
    protected View $view;
    protected ?\App\Core\Session $session;

    public function __construct(?View $view = null, ?\App\Core\Session $session = null)
    {
        $this->view = $view ?? new View();
        $this->session = $session;
    }

    protected function render(
        string $template,
        array $variables = [],
        ?string $layout = 'layout',
        int $status = 200
    ): Response {
        $html = $this->view->render($template, $variables, $layout);
        return new Response($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    protected function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function currentUser(): ?object
    {
        if ($this->session !== null) {
            $user = $this->session->get('user');
            return is_object($user) ? $user : null;
        }

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && is_object($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        return null;
    }
}
