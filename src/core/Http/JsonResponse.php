<?php

declare(strict_types=1);

namespace App\Core\Http;

final class JsonResponse extends Response
{
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($content === false) {
            $content = '{}';
        }

        $headers['Content-Type'] = 'application/json; charset=utf-8';
        parent::__construct($content, $statusCode, $headers);
    }
}
