<?php

declare(strict_types=1);

$isLoggedIn = isset($_SESSION['user_id']);
?>
<nav class="app-nav" aria-label="Nawigacja główna">
    <ul class="nav-list">
        <li><a href="/" class="nav-link">Strona główna</a></li>
        <li><a href="/projects" class="nav-link">Talie / Projekty</a></li>
        <?php if ($isLoggedIn): ?>
            <li>
                <form action="/logout" method="POST" class="logout-form" style="display:inline;">
                    <button type="submit" class="nav-link btn-link">Wyloguj się</button>
                </form>
            </li>
        <?php else: ?>
            <li><a href="/login" class="nav-link">Logowanie</a></li>
            <li><a href="/register" class="nav-link">Rejestracja</a></li>
        <?php endif; ?>
    </ul>
</nav>
