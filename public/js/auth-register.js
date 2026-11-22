/**
 * Registration view client-side validation and form handling (KAN-43)
 * ES6 Module
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    if (!form) {
        return;
    }

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('register-submit-btn');
    const authAlert = document.getElementById('auth-alert');

    const usernameError = document.getElementById('username-error');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const passwordConfirmError = document.getElementById('password_confirmation-error');

    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setError(input, errorElement, message) {
        input.setAttribute('aria-invalid', 'true');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function clearError(input, errorElement) {
        input.removeAttribute('aria-invalid');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }

    function clearAllErrors() {
        clearError(usernameInput, usernameError);
        clearError(emailInput, emailError);
        clearError(passwordInput, passwordError);
        clearError(passwordConfirmInput, passwordConfirmError);
        if (authAlert) {
            authAlert.style.display = 'none';
            authAlert.textContent = '';
        }
    }

    usernameInput?.addEventListener('input', () => clearError(usernameInput, usernameError));
    emailInput?.addEventListener('input', () => clearError(emailInput, emailError));
    passwordInput?.addEventListener('input', () => clearError(passwordInput, passwordError));
    passwordConfirmInput?.addEventListener('input', () => clearError(passwordConfirmInput, passwordConfirmError));

    form.addEventListener('submit', (e) => {
        clearAllErrors();
        let isValid = true;
        let firstInvalidInput = null;

        const username = usernameInput?.value.trim() ?? '';
        const email = emailInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';
        const passwordConfirm = passwordConfirmInput?.value ?? '';

        if (!username) {
            setError(usernameInput, usernameError, 'Nazwa użytkownika jest wymagana.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || usernameInput;
        } else if (username.length < 3) {
            setError(usernameInput, usernameError, 'Nazwa użytkownika musi mieć co najmniej 3 znaki.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || usernameInput;
        } else if (username.length > 50) {
            setError(usernameInput, usernameError, 'Nazwa użytkownika nie może przekraczać 50 znaków.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || usernameInput;
        }

        if (!email) {
            setError(emailInput, emailError, 'Adres e-mail jest wymagany.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || emailInput;
        } else if (!EMAIL_REGEX.test(email)) {
            setError(emailInput, emailError, 'Podaj prawidłowy adres e-mail (np. user@example.com).');
            isValid = false;
            firstInvalidInput = firstInvalidInput || emailInput;
        }

        if (!password) {
            setError(passwordInput, passwordError, 'Hasło jest wymagane.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || passwordInput;
        } else if (password.length < 8) {
            setError(passwordInput, passwordError, 'Hasło musi mieć co najmniej 8 znaków.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || passwordInput;
        }

        if (!passwordConfirm) {
            setError(passwordConfirmInput, passwordConfirmError, 'Powtórzenie hasła jest wymagane.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || passwordConfirmInput;
        } else if (password !== passwordConfirm) {
            setError(passwordConfirmInput, passwordConfirmError, 'Hasła nie są identyczne.');
            isValid = false;
            firstInvalidInput = firstInvalidInput || passwordConfirmInput;
        }

        if (!isValid) {
            e.preventDefault();
            firstInvalidInput?.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Rejestracja...';
        }
    });
});
