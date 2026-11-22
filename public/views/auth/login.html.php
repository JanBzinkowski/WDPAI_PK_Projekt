<?php

declare(strict_types=1);
?>
<section class="auth-container">
    <div class="auth-card" role="region" aria-labelledby="login-title">
        <header class="auth-header">
            <h1 id="login-title">Zaloguj się</h1>
            <p class="auth-subtitle">Witaj ponownie! Wprowadź dane, aby przejść do swoich fiszek.</p>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?= e($success) ?>
            </div>
        <?php elseif (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
            <div class="alert alert-success" role="alert">
                Rejestracja zakończona sukcesem! Możesz się teraz zalogować.
            </div>
        <?php elseif (isset($_GET['logged_out']) && $_GET['logged_out'] === '1'): ?>
            <div class="alert alert-success" role="alert">
                Wylogowano pomyślnie.
            </div>
        <?php endif; ?>

        <div id="auth-alert" class="alert alert-danger" style="display: none;" role="alert" aria-live="polite"></div>

        <form id="login-form" action="/login" method="POST" novalidate>
            <?= \App\Core\CsrfToken::field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Adres e-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="np. jan@example.com"
                    autocomplete="email"
                    value="<?= e($email ?? '') ?>"
                    required
                >
                <div class="form-error-msg" id="email-error"></div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Hasło</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
                <div class="form-error-msg" id="password-error"></div>
            </div>

            <div class="form-actions-secondary" style="display: flex; justify-content: flex-end; margin-bottom: 1.25rem;">
                <a href="#forgot-password" class="auth-link-secondary" style="font-size: 0.875rem; color: var(--text-secondary); text-decoration: none;">
                    Nie pamiętasz hasła?
                </a>
            </div>

            <button type="submit" class="btn btn-primary" id="login-submit-btn">
                Zaloguj się
            </button>
        </form>

        <footer class="auth-footer">
            <p>Nie masz jeszcze konta? <a href="/register">Zarejestruj się</a></p>
        </footer>
    </div>
</section>

<script src="/js/auth-login.js" type="module"></script>
