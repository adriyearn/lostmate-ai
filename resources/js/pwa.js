/*
 * Installable app support.
 *  1. Registers the service worker (public/sw.js), which provides the
 *     offline page. Browsers only allow this on HTTPS or localhost.
 *  2. Shows our own "Install app" button when the browser says the app can
 *     be installed (Chrome/Edge/Android). On iPhone, users install with
 *     Share -> "Add to Home Screen" instead.
 */

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* not supported here (e.g. plain http) - the site still works normally */
        });
    });
}

let installPrompt = null;
const installButtons = document.querySelectorAll('.lm-install-app');

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault(); // keep the event so our button can trigger it
    installPrompt = event;
    installButtons.forEach((button) => button.classList.remove('d-none'));
});

installButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        if (!installPrompt) {
            return;
        }
        installPrompt.prompt();
        await installPrompt.userChoice;
        installPrompt = null;
        installButtons.forEach((b) => b.classList.add('d-none'));
    });
});

window.addEventListener('appinstalled', () => {
    installButtons.forEach((button) => button.classList.add('d-none'));
});
