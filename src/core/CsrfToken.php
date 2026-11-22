<?php

declare(strict_types=1);

namespace App\Core;

final class CsrfToken
{
    public static function generate(Session $session): string
    {
        $token = $session->get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $session->set('_csrf_token', $token);
        }

        return $token;
    }

    public static function validate(Session $session, ?string $token): bool
    {
        $expected = $session->get('_csrf_token');
        if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }
}
