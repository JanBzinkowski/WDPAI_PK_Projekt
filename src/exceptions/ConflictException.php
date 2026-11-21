<?php

declare(strict_types=1);

namespace App\Exception;

class ConflictException extends AppException
{
    public function __construct(string $message = 'Conflict', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
