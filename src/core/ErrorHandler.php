<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\JsonResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Exception\AppException;
use App\Exception\ConflictException;
use App\Exception\DomainException;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use Throwable;

final class ErrorHandler
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function register(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e): void {
            $response = $this->handle($e, Request::createFromGlobals());
            $response->send();
        });
    }

    public function handle(Throwable $e, ?Request $request = null): Response
    {
        $this->logException($e);

        $request = $request ?? Request::createFromGlobals();
        $code = $this->resolveStatusCode($e);
        $message = $this->resolveMessage($e, $code);

        if ($request->expectsJson() || $request->isJson()) {
            return $this->formatJsonResponse($e, $code, $message);
        }

        return $this->formatHtmlResponse($e, $code, $message);
    }

    public function resolveStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof ValidationException => 400,
            $e instanceof UnauthorizedException => 401,
            $e instanceof ForbiddenException => 403,
            $e instanceof NotFoundException => 404,
            $e instanceof ConflictException => 409,
            $e instanceof DomainException => 422,
            $e instanceof AppException && $e->getCode() >= 400 && $e->getCode() < 600 => $e->getCode(),
            default => 500,
        };
    }

    private function resolveMessage(Throwable $e, int $statusCode): string
    {
        $isDev = $this->config->isDebug() || $this->config->isDev();

        if ($statusCode === 500 && !$isDev) {
            return 'Wystąpił nieoczekiwany błąd serwera. Spróbuj ponownie później.';
        }

        return $e->getMessage() !== '' ? $e->getMessage() : $this->getDefaultStatusText($statusCode);
    }

    private function formatJsonResponse(Throwable $e, int $code, string $message): JsonResponse
    {
        $payload = [
            'success' => false,
            'status' => $code,
            'message' => $message,
        ];

        if ($e instanceof ValidationException) {
            $payload['errors'] = $e->getErrors();
        }

        if ($this->config->isDebug() || $this->config->isDev()) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        return new JsonResponse($payload, $code);
    }

    private function formatHtmlResponse(Throwable $e, int $code, string $message): Response
    {
        $viewsDir = dirname(__DIR__, 2) . '/public/views';
        $specificView = $viewsDir . '/errors/' . $code . '.html.php';
        $genericView = $viewsDir . '/errors/error.html.php';

        $template = is_file($specificView) ? $specificView : (is_file($genericView) ? $genericView : null);

        $isDev = $this->config->isDebug() || $this->config->isDev();
        $variables = [
            'statusCode' => $code,
            'statusText' => $this->getDefaultStatusText($code),
            'message' => $message,
            'isDev' => $isDev,
            'exception' => $e,
        ];

        if ($template !== null) {
            extract($variables, EXTR_SKIP);
            ob_start();
            require $template;
            $html = (string) ob_get_clean();
        } else {
            $html = "<h1>Błąd {$code}: {$this->getDefaultStatusText($code)}</h1><p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        return new Response($html, $code, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function getDefaultStatusText(int $code): string
    {
        return match ($code) {
            400 => 'Nieprawidłowe żądanie (Bad Request)',
            401 => 'Nieautoryzowany dostęp (Unauthorized)',
            403 => 'Brak uprawnień (Forbidden)',
            404 => 'Nie znaleziono strony (Not Found)',
            405 => 'Niedozwolona metoda HTTP (Method Not Allowed)',
            409 => 'Konflikt zasobów (Conflict)',
            422 => 'Błąd logiki biznesowej (Unprocessable Entity)',
            default => 'Wewnętrzny błąd serwera (Internal Server Error)',
        };
    }

    private function logException(Throwable $e): void
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        error_log($logMessage);
    }
}
