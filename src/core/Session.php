<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private Config $config;
    private int $idleTimeout;

    public function __construct(Config $config, int $idleTimeout = 1800)
    {
        $this->config = $config;
        $this->idleTimeout = $idleTimeout;
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $this->config->isProd(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->checkIdleTimeout();
        }
    }

    public function regenerate(bool $destroyOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($destroyOld);
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION['_flash'][$key])) {
            return $default;
        }

        $val = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $val;
    }

    public function destroy(): void
    {
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

    private function checkIdleTimeout(): void
    {
        $now = time();
        $lastActivity = $this->get('_last_activity');

        if ($lastActivity !== null && is_numeric($lastActivity)) {
            if (($now - (int) $lastActivity) > $this->idleTimeout) {
                $this->destroy();
                return;
            }
        }

        $this->set('_last_activity', $now);
    }
}
