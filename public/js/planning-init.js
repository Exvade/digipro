// Planning Page - Variable Setup and CSRF Configuration
(function() {
    'use strict';
    
    // Setup CSRF token for AJAX requests
    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content
                }
            });
        }
    }
    
    // Initialize global variables from JSON data
    document.addEventListener('DOMContentLoaded', function() {
        const planningDataScript = document.getElementById('planning-data');
        
        if (planningDataScript) {
            try {
                // Parse JSON from script tag
                const data = JSON.parse(planningDataScript.textContent);
                
                // Set global variables
                window.successMessage = data.successMessage;
                window.planningIndexUrl = data.indexUrl || '';
                window.planningCetakUrl = data.cetakUrl || '';
                window.todayYmd = data.todayYmd || '';
                window.tomorrowYmd = data.tomorrowYmd || '';
                window.currentFilter = data.currentFilter;
                
            } catch (e) {
                console.error('Error parsing planning data:', e);
                // Set default values
                window.successMessage = null;
                window.planningIndexUrl = '';
                window.planningCetakUrl = '';
                window.todayYmd = '';
                window.tomorrowYmd = '';
                window.currentFilter = null;
            }
        }
    });
})();
