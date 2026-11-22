<?php

declare(strict_types=1);

namespace App\Service;

final class LoginRateLimiter
{
    private int $maxAttempts;
    private int $decaySeconds;
    /** @var array<string, array{count: int, expires_at: int}> */
    private static array $attempts = [];

    public function __construct(int $maxAttempts = 5, int $decaySeconds = 900)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function isTooManyAttempts(string $email, string $ip): bool
    {
        $key = $this->key($email, $ip);
        $now = time();

        if (isset(self::$attempts[$key])) {
            if (self::$attempts[$key]['expires_at'] < $now) {
                unset(self::$attempts[$key]);
                return false;
            }

            return self::$attempts[$key]['count'] >= $this->maxAttempts;
        }

        return false;
    }

    public function hit(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        $now = time();

        if (isset(self::$attempts[$key]) && self::$attempts[$key]['expires_at'] >= $now) {
            self::$attempts[$key]['count']++;
        } else {
            self::$attempts[$key] = [
                'count' => 1,
                'expires_at' => $now + $this->decaySeconds,
            ];
        }
    }

    public function clear(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        unset(self::$attempts[$key]);
    }

    public static function reset(): void
    {
        self::$attempts = [];
    }

    private function key(string $email, string $ip): string
    {
        return sha1(strtolower(trim($email)) . '|' . trim($ip));
    }
}
