// =========================================
// Dark Mode Toggle Functionality
// =========================================
(function() {
    'use strict';

    // Function to initialize dark mode
    function initDarkMode() {
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        const darkModeIcon = document.getElementById('darkModeIcon');
        
        // Check if elements exist
        if (!darkModeSwitch) {
            return;
        }

        // Check for saved dark mode preference
        const isDarkMode = localStorage.getItem('darkMode') === 'enabled';

        // Apply saved preference on page load
        if (isDarkMode) {
            enableDarkMode();
        }

        // Toggle dark mode on switch change
        darkModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        });

        function enableDarkMode() {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
            darkModeSwitch.checked = true;
            if (darkModeIcon) {
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
            }
            localStorage.setItem('darkMode', 'enabled');
        }

        function disableDarkMode() {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
            darkModeSwitch.checked = false;
            if (darkModeIcon) {
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
            }
            localStorage.setItem('darkMode', 'disabled');
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }

    // Also support jQuery if available
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(initDarkMode);
    }
})();
