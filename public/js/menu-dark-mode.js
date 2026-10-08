// =========================================
// Dark Mode Toggle for Menu Page (Tailwind & Standard Compatible)
// =========================================
(function() {
    'use strict';

    function isDarkActive() {
        return localStorage.getItem('darkMode') === 'enabled';
    }

    function applyTheme(isDark) {
        const html = document.documentElement;
        const body = document.body;
        const toggle = document.getElementById('darkModeToggle');

        if (isDark) {
            html.classList.add('dark', 'dark-mode');
            if (body) {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
            }
            if (toggle) toggle.checked = true;
            localStorage.setItem('darkMode', 'enabled');
        } else {
            html.classList.remove('dark', 'dark-mode');
            if (body) {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
            }
            if (toggle) toggle.checked = false;
            localStorage.setItem('darkMode', 'disabled');
        }
    }

    // Run as early as possible
    applyTheme(isDarkActive());

    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('darkModeToggle');
        const toggleLabel = document.querySelector('.toggle-switch');
        const toggleBtn = document.getElementById('menuThemeToggleBtn');

        // Apply state again once DOM is ready
        applyTheme(isDarkActive());

        if (toggle) {
            toggle.addEventListener('change', function() {
                applyTheme(this.checked);
            });
        }

        // Direct click fallback on toggle switch label
        if (toggleLabel && toggle) {
            toggleLabel.addEventListener('click', function(e) {
                // If the target wasn't the checkbox directly, toggle it
                if (e.target !== toggle) {
                    e.preventDefault();
                    const nextState = !isDarkActive();
                    applyTheme(nextState);
                }
            });
        }

        // Button toggle fallback
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                applyTheme(!isDarkActive());
            });
        }

        // Keyboard shortcut: Ctrl+D or Cmd+D
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                applyTheme(!isDarkActive());
            }
        });
    });
})();

