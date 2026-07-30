(function () {
    var STORAGE_KEY = 'jira-logger-theme';
    var root = document.documentElement;

    function getStoredTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }

    function updateToggleUI(theme) {
        document.querySelectorAll('.theme-toggle').forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            }
            btn.title = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        });
    }

    // Applied synchronously (before stylesheets/body render) to avoid a flash of the wrong theme.
    root.setAttribute('data-theme', getStoredTheme());

    window.toggleTheme = function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        localStorage.setItem(STORAGE_KEY, next);
        updateToggleUI(next);
        document.dispatchEvent(new CustomEvent('themechanged', { detail: { theme: next } }));
    };

    document.addEventListener('DOMContentLoaded', function () {
        updateToggleUI(root.getAttribute('data-theme'));
    });
})();
