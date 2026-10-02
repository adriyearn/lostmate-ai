/*
 * Light/dark mode toggle (the sun/moon button in the navbar).
 * The starting theme is set by layouts/partials/theme.blade.php; this file
 * only handles switching and remembering the choice.
 */

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme);
}

function savedTheme() {
    try {
        return localStorage.getItem('lm-theme');
    } catch (e) {
        return null;
    }
}

document.querySelectorAll('.lm-theme-toggle').forEach((button) => {
    button.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        try {
            localStorage.setItem('lm-theme', next);
        } catch (e) {
            /* still switches for this page view */
        }
    });
});

// Until the user picks a theme themselves, follow the device setting live.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if (!savedTheme()) {
        applyTheme(event.matches ? 'dark' : 'light');
    }
});
