<?php

declare(strict_types=1);

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Router;

final class Routing extends Router
{
    /**
     * Backward-compatible dispatch accepting method and URI strings
     */
    public function dispatchLegacy(string $method, string $uri): bool
    {
        $request = new Request($method, $uri);
        $response = parent::dispatch($request);
        $response->send();
        return $response->getStatusCode() !== 404;
    }
}
