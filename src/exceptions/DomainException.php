<?php

declare(strict_types=1);

namespace App\Exception;

class DomainException extends AppException
{
    public function __construct(string $message = 'Domain business logic error', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
