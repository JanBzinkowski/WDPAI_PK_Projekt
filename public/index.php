<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Bootstrap;
use App\Core\Http\Request;

$app = Bootstrap::boot();
$response = $app->handle(Request::createFromGlobals());
$response->send();
