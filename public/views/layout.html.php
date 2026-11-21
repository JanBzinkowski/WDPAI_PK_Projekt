<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Flashcards Leitner') ?></title>
    <script>
        (function() {
            var savedTheme = localStorage.getItem('theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = savedTheme || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body class="app-body">
    <?= $this->renderPartial('partials/header', $variables ?? []) ?>
    <?= $this->renderPartial('partials/nav', $variables ?? []) ?>

    <main class="main-content" id="main-content">
        <?= $content ?? '' ?>
    </main>

    <?= $this->renderPartial('partials/footer', $variables ?? []) ?>
    <script src="/js/main.js" type="module"></script>
</body>
</html>
