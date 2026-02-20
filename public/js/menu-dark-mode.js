// =========================================
// Dark Mode Toggle for Menu Page
// =========================================
(function() {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        initDarkModeToggle();
    });

    function initDarkModeToggle() {
        const $darkModeToggle = $('#darkModeToggle');
        const $body = $('body');

        // Check if toggle exists
        if ($darkModeToggle.length === 0) {
            console.warn('Dark mode toggle not found');
            return;
        }

        // Check for saved dark mode preference (unified key)
        const isDarkMode = localStorage.getItem('darkMode') === 'enabled';

        // Apply saved preference on page load
        if (isDarkMode) {
            enableDarkMode();
        }

        // Toggle dark mode on switch change
        $darkModeToggle.on('change', function() {
            if ($(this).is(':checked')) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        });

        // Enable dark mode function
        function enableDarkMode() {
            $body.addClass('dark-mode');
            $darkModeToggle.prop('checked', true);
            localStorage.setItem('darkMode', 'enabled');
            
            // Optional: Add smooth transition effect
            $body.css('transition', 'background-color 0.3s ease, color 0.3s ease');
            
            // Log for debugging
            console.log('Dark mode enabled');
        }

        // Disable dark mode function
        function disableDarkMode() {
            $body.removeClass('dark-mode');
            $darkModeToggle.prop('checked', false);
            localStorage.setItem('darkMode', 'disabled');
            
            // Optional: Add smooth transition effect
            $body.css('transition', 'background-color 0.3s ease, color 0.3s ease');
            
            // Log for debugging
            console.log('Dark mode disabled');
        }

        // Optional: Add keyboard shortcut (Ctrl/Cmd + D)
        $(document).on('keydown', function(e) {
            // Check if Ctrl (Windows/Linux) or Cmd (Mac) + D is pressed
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                $darkModeToggle.prop('checked', !$darkModeToggle.prop('checked')).trigger('change');
            }
        });

        // Optional: Detect system preference
        if (window.matchMedia) {
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Only apply system preference if user hasn't set a preference yet
            if (localStorage.getItem('darkMode') === null) {
                if (systemPrefersDark.matches) {
                    enableDarkMode();
                }
            }

            // Listen for system preference changes
            systemPrefersDark.addEventListener('change', function(e) {
                // Only auto-switch if user hasn't manually set preference
                if (localStorage.getItem('darkMode') === null) {
                    if (e.matches) {
                        enableDarkMode();
                    } else {
                        disableDarkMode();
                    }
                }
            });
        }
    }

    // Optional: Expose functions globally if needed
    window.menuDarkMode = {
        enable: function() {
            $('#darkModeToggle').prop('checked', true).trigger('change');
        },
        disable: function() {
            $('#darkModeToggle').prop('checked', false).trigger('change');
        },
        toggle: function() {
            $('#darkModeToggle').prop('checked', !$('#darkModeToggle').prop('checked')).trigger('change');
        },
        isEnabled: function() {
            return localStorage.getItem('darkMode') === 'enabled';
        }
    };

})();
