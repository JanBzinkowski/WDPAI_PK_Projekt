<?php

declare(strict_types=1);

final readonly class Config
{
    public function get(string $key, ?string $default = null): string
    {
        return $this->getString($key, $default);
    }

    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->readRaw($key);

        if ($value === null || trim($value) === '') {
            if ($default !== null) {
                return $default;
            }

            throw new \RuntimeException(
                sprintf('Missing required environment variable: %s', $key),
            );
        }

        return $value;
    }

    public function getInt(string $key, ?int $default = null): int
    {
        $value = $this->readRaw($key);

        if ($value === null || trim($value) === '') {
            if ($default !== null) {
                return $default;
            }

            throw new \RuntimeException(
                sprintf('Missing required environment variable: %s', $key),
            );
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT);

        if ($filtered === false) {
            throw new \RuntimeException(
                sprintf('Environment variable "%s" must be a valid integer, "%s" given.', $key, $value),
            );
        }

        return $filtered;
    }

    public function getBool(string $key, ?bool $default = null): bool
    {
        $value = $this->readRaw($key);

        if ($value === null || trim($value) === '') {
            if ($default !== null) {
                return $default;
            }

            throw new \RuntimeException(
                sprintf('Missing required environment variable: %s', $key),
            );
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => throw new \RuntimeException(
                sprintf('Environment variable "%s" must be a valid boolean, "%s" given.', $key, $value),
            ),
        };
    }

    public function getAppEnv(): string
    {
        return $this->getString('APP_ENV');
    }

    public function isDebug(): bool
    {
        return $this->getBool('APP_DEBUG', false);
    }

    public function isProduction(): bool
    {
        return $this->getAppEnv() === 'production';
    }

    public function getAppUrl(): string
    {
        return $this->getString('APP_URL');
    }

    public function getAppTimezone(): string
    {
        return $this->getString('APP_TIMEZONE');
    }

    public function getAppPort(): int
    {
        return $this->getInt('APP_PORT', 8080);
    }

    public function getDbHost(): string
    {
        return $this->getString('DB_HOST');
    }

    public function getDbPort(): int
    {
        return $this->getInt('DB_PORT', 5432);
    }

    public function getDbName(): string
    {
        return $this->getFirstAvailableString(['DB_NAME', 'POSTGRES_DB']);
    }

    public function getDbUser(): string
    {
        return $this->getFirstAvailableString(['DB_USER', 'POSTGRES_USER']);
    }

    public function getDbPassword(): string
    {
        return $this->getFirstAvailableString(['DB_PASSWORD', 'POSTGRES_PASSWORD']);
    }

    public function getDbDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->getDbHost(),
            $this->getDbPort(),
            $this->getDbName(),
        );
    }

    public function getSmtpHost(): string
    {
        return $this->getString('SMTP_HOST');
    }

    public function getSmtpPort(): int
    {
        return $this->getInt('SMTP_PORT');
    }

    public function getSmtpUser(): string
    {
        return $this->getString('SMTP_USER', '');
    }

    public function getSmtpPassword(): string
    {
        $password = $this->readRaw('SMTP_PASSWORD') ?? $this->readRaw('SMTP_PASS');

        if ($password === null) {
            return '';
        }

        return $password;
    }

    public function getSmtpEncryption(): string
    {
        return $this->getString('SMTP_ENCRYPTION', '');
    }

    public function getSmtpFromEmail(): string
    {
        return $this->getString('SMTP_FROM_EMAIL');
    }

    public function getSmtpFromName(): string
    {
        return $this->getString('SMTP_FROM_NAME', 'WDPAI Flashcards');
    }

    /**
     * @param list<string> $keys
     */
    private function getFirstAvailableString(array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->readRaw($key);
            if ($value !== null && trim($value) !== '') {
                return $value;
            }
        }

        throw new \RuntimeException(
            sprintf('Missing required environment variable (tried: %s)', implode(', ', $keys)),
        );
    }

    private function readRaw(string $key): ?string
    {
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        if (isset($_ENV[$key]) && is_scalar($_ENV[$key])) {
            return (string) $_ENV[$key];
        }

        return null;
    }
}
