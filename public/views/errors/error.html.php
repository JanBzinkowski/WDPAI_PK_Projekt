<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) ($statusCode ?? 500)) ?> - <?= htmlspecialchars((string) ($statusText ?? 'Błąd')) ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .error-card { background: #1e293b; border-radius: 12px; max-width: 540px; width: 100%; padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); text-align: center; }
        .error-code { font-size: 4rem; font-weight: 800; color: #ef4444; margin: 0; }
        .error-title { font-size: 1.5rem; margin: 0.5rem 0 1rem; }
        .error-desc { color: #94a3b8; line-height: 1.5; margin-bottom: 1.5rem; }
        .btn-home { display: inline-block; background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-home:hover { background: #2563eb; }
        .debug-box { text-align: left; background: #0f172a; border-radius: 6px; padding: 1rem; margin-top: 1.5rem; font-family: monospace; font-size: 0.85rem; overflow-x: auto; color: #fca5a5; }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="error-code"><?= htmlspecialchars((string) ($statusCode ?? 500)) ?></h1>
        <h2 class="error-title"><?= htmlspecialchars((string) ($statusText ?? 'Wystąpił błąd')) ?></h2>
        <p class="error-desc"><?= htmlspecialchars((string) ($message ?? 'Nieoczekiwany problem.')) ?></p>
        <a href="/" class="btn-home">Wróć na stronę główną</a>

        <?php if (!empty($isDev) && !empty($exception)): ?>
            <div class="debug-box">
                <strong>Exception:</strong> <?= htmlspecialchars(get_class($exception)) ?><br>
                <strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?>:<?= $exception->getLine() ?><br>
                <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
