<?php

declare(strict_types=1);

namespace App\Core\Http;

final class RedirectResponse extends Response
{
    public function __construct(string $url, int $statusCode = 302, array $headers = [])
    {
        $headers['Location'] = $url;
        parent::__construct('', $statusCode, $headers);
    }
}
