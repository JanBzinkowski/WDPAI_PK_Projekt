<?php

declare(strict_types=1);

namespace App\Exception;

class ValidationException extends AppException
{
    /** @var array<string, string|array<string>> */
    private array $errors;

    /**
     * @param array<string, string|array<string>> $errors
     */
    public function __construct(array $errors = [], string $message = 'Validation error', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<string, string|array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
