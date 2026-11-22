/**
 * Main application script - Flashcards Leitner
 * ES6 Module
 */

document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle handling
    const themeToggleBtn = document.querySelector('[data-theme-toggle]');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

    // Dismissable alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                alert.style.display = 'none';
            });
        }
    });
});
