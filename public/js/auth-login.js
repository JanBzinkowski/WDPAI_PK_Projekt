/**
 * Login view client-side validation and form handling (KAN-42)
 * ES6 Module
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    if (!form) {
        return;
    }

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('login-submit-btn');
    const authAlert = document.getElementById('auth-alert');

    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

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
        clearError(emailInput, emailError);
        clearError(passwordInput, passwordError);
        if (authAlert) {
            authAlert.style.display = 'none';
            authAlert.textContent = '';
        }
    }

    emailInput?.addEventListener('input', () => clearError(emailInput, emailError));
    passwordInput?.addEventListener('input', () => clearError(passwordInput, passwordError));

    form.addEventListener('submit', (e) => {
        clearAllErrors();
        let isValid = true;
        let firstInvalidInput = null;

        const email = emailInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';

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
        }

        if (!isValid) {
            e.preventDefault();
            firstInvalidInput?.focus();
            return;
        }

        // Show loading state on button during submission
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logowanie...';
        }
    });
});
