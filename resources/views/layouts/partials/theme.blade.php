{{--
    Picks light or dark mode BEFORE the page is drawn, so dark-mode users
    never see a white flash. Uses the choice saved by the navbar toggle,
    or the device's own setting if the user never chose.
    Must be inline in <head> (not in app.js, which loads later).
--}}
<script>
    (function () {
        var saved = null;
        try { saved = localStorage.getItem('lm-theme'); } catch (e) { /* private mode */ }
        var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
