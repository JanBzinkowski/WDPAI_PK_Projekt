<?php

declare(strict_types=1);

namespace App\Exception;

class UnauthorizedException extends AppException
{
    public function __construct(string $message = 'Unauthorized', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
