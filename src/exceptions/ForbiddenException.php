<?php

declare(strict_types=1);

namespace App\Exception;

class ForbiddenException extends AppException
{
    public function __construct(string $message = 'Forbidden', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
