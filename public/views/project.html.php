<?php

declare(strict_types=1);
?>
<section class="page-project">
    <h1><?= e($pageTitle ?? 'Szczegóły projektu') ?></h1>
    <p>Identyfikator projektu: <strong><?= e((string) ($projectId ?? '')) ?></strong></p>
    <a href="/projects" class="btn btn-secondary">&larr; Powrót do listy projektów</a>
</section>
