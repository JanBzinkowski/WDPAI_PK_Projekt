<?php

declare(strict_types=1);
?>
<section class="auth-container">
    <div class="auth-card" role="region" aria-labelledby="register-title">
        <header class="auth-header">
            <h1 id="register-title">Utwórz konto</h1>
            <p class="auth-subtitle">Dołącz do Flashcards Leitner i ucz się skuteczniej dzięki inteligentnemu systemowi powtórek.</p>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div id="auth-alert" class="alert alert-danger" style="display: none;" role="alert" aria-live="polite"></div>

        <form id="register-form" action="/register" method="POST" novalidate>
            <?= \App\Core\CsrfToken::field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Nazwa użytkownika</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-input"
                    placeholder="np. jan_kowalski"
                    autocomplete="username"
                    value="<?= e($username ?? '') ?>"
                    minlength="3"
                    maxlength="50"
                    required
                >
                <div class="form-error-msg" id="username-error"></div>
            </div>

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
                    placeholder="Minimum 8 znaków"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <div class="form-error-msg" id="password-error"></div>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Powtórz hasło</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Powtórz wprowadzone hasło"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <div class="form-error-msg" id="password_confirmation-error"></div>
            </div>

            <button type="submit" class="btn btn-primary" id="register-submit-btn">
                Zarejestruj się
            </button>
        </form>

        <footer class="auth-footer">
            <p>Masz już konto? <a href="/login">Zaloguj się</a></p>
        </footer>
    </div>
</section>

<script src="/js/auth-register.js" type="module"></script>
