// Dark Mode Toggle Functionality for Planning Page
document.addEventListener('DOMContentLoaded', function() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const body = document.body;

    // Check if elements exist
    if (!darkModeSwitch || !darkModeIcon) {
        console.warn('Dark mode elements not found');
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

    /**
     * Enable dark mode
     */
    function enableDarkMode() {
        body.classList.add('dark-mode');
        darkModeSwitch.checked = true;
        darkModeIcon.classList.remove('fa-moon');
        darkModeIcon.classList.add('fa-sun');
        localStorage.setItem('darkMode', 'enabled');
    }

    /**
     * Disable dark mode
     */
    function disableDarkMode() {
        body.classList.remove('dark-mode');
        darkModeSwitch.checked = false;
        darkModeIcon.classList.remove('fa-sun');
        darkModeIcon.classList.add('fa-moon');
        localStorage.setItem('darkMode', 'disabled');
    }
});
