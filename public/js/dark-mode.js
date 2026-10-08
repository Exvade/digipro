// =========================================
// Global Dark Mode & Layout Interactive Controller (Tailwind v3 Compatible)
// =========================================
(function() {
    'use strict';

    function setDarkMode(enable) {
        const html = document.documentElement;
        const body = document.body;

        if (enable) {
            html.classList.add('dark', 'dark-mode');
            if (body) body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            html.classList.remove('dark', 'dark-mode');
            if (body) body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
        }

        // Sync all toggle elements on page
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeSwitch) darkModeSwitch.checked = enable;
        if (darkModeToggle) darkModeToggle.checked = enable;
    }

    // Apply immediately upon script execution
    const isSavedDark = localStorage.getItem('darkMode') === 'enabled';
    setDarkMode(isSavedDark);

    function initDarkModeControls() {
        // Re-apply once body is guaranteed to exist
        const isDark = localStorage.getItem('darkMode') === 'enabled';
        setDarkMode(isDark);

        const darkModeSwitch = document.getElementById('darkModeSwitch');
        const darkModeToggle = document.getElementById('darkModeToggle');
        const headerDarkModeToggle = document.getElementById('headerDarkModeToggle');

        if (darkModeSwitch) {
            darkModeSwitch.checked = isDark;
            darkModeSwitch.addEventListener('change', function() {
                setDarkMode(this.checked);
            });
        }

        if (darkModeToggle) {
            darkModeToggle.checked = isDark;
            darkModeToggle.addEventListener('change', function() {
                setDarkMode(this.checked);
            });
        }

        if (headerDarkModeToggle) {
            headerDarkModeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isCurrentlyDark = document.documentElement.classList.contains('dark') ||
                                        document.documentElement.classList.contains('dark-mode');
                setDarkMode(!isCurrentlyDark);
            });
        }
    }

    function initLayoutInteractions() {
        // Sidebar drawer toggle
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const mainSidebar = document.getElementById('mainSidebar');
        if (sidebarToggleBtn && mainSidebar) {
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                mainSidebar.classList.toggle('hidden');
            });
        }

        // User dropdown
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userDropdownBtn && userDropdownMenu) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
                const notifMenu = document.getElementById('notifDropdownMenu');
                if (notifMenu) notifMenu.classList.add('hidden');
            });
        }

        // Notification dropdown
        const notifDropdownBtn = document.getElementById('notifDropdownBtn');
        const notifDropdownMenu = document.getElementById('notifDropdownMenu');
        if (notifDropdownBtn && notifDropdownMenu) {
            notifDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdownMenu.classList.toggle('hidden');
                if (userDropdownMenu) userDropdownMenu.classList.add('hidden');
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function() {
            if (userDropdownMenu) userDropdownMenu.classList.add('hidden');
            if (notifDropdownMenu) notifDropdownMenu.classList.add('hidden');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initDarkModeControls();
            initLayoutInteractions();
        });
    } else {
        initDarkModeControls();
        initLayoutInteractions();
    }
})();
