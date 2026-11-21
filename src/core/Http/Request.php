<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array $query;
    private array $body;
    private array $headers;
    private array $files;
    private array $cookies;
    private array $server;

    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $body = [],
        array $headers = [],
        array $files = [],
        array $cookies = [],
        array $server = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->query = $query;
        $this->body = $body;
        $this->headers = $headers;
        $this->files = $files;
        $this->cookies = $cookies;
        $this->server = $server;
    }

    public static function createFromGlobals(): self
    {
        $server = $_SERVER;
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $uri = $server['REQUEST_URI'] ?? '/';
        $query = $_GET;
        $body = $_POST;
        $cookies = $_COOKIE;
        $files = $_FILES;

        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = (string) $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name = str_replace('_', '-', strtolower($key));
                $headers[$name] = (string) $value;
            }
        }

        if (isset($body['_method']) && in_array(strtoupper((string) $body['_method']), ['PUT', 'PATCH', 'DELETE'], true)) {
            $method = strtoupper((string) $body['_method']);
            unset($body['_method']);
        }

        $contentType = $headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            if ($rawInput !== false && $rawInput !== '') {
                $decoded = json_decode($rawInput, true);
                if (is_array($decoded)) {
                    $body = array_merge($body, $decoded);
                }
            }
        }

        return new self($method, $uri, $query, $body, $headers, $files, $cookies, $server);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function getBody(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }

        return $this->body[$key] ?? $default;
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getFiles(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }

        return $this->files[$key] ?? null;
    }

    public function getCookies(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->cookies;
        }

        return $this->cookies[$key] ?? $default;
    }

    public function getServer(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }

        return $this->server[$key] ?? $default;
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeader('content-type', '');
        return str_contains((string) $contentType, 'application/json');
    }

    public function expectsJson(): bool
    {
        $accept = $this->getHeader('accept', '');
        return str_contains((string) $accept, 'application/json');
    }
}
