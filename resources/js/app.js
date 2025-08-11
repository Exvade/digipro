// ====== ADMIN PANEL RESPONSIVE JAVASCRIPT ======

$(document).ready(function() {
    
    // ====== MOBILE SIDEBAR HANDLING ======
    
    // Check if device is mobile
    function isMobile() {
        return window.innerWidth <= 991.98;
    }
    
    // Initialize sidebar behavior
    function initSidebar() {
        const sidebar = $('#mainSidebar');
        const overlay = $('#sidebarOverlay');
        const toggleBtn = $('#sidebarToggle');
        
        // Show sidebar on mobile when toggle is clicked
        toggleBtn.on('click', function(e) {
            e.preventDefault();
            
            if (isMobile()) {
                sidebar.addClass('show');
                overlay.addClass('show');
                $('body').addClass('sidebar-open');
            }
        });
        
        // Hide sidebar when overlay is clicked
        overlay.on('click', function() {
            sidebar.removeClass('show');
            overlay.removeClass('show');
            $('body').removeClass('sidebar-open');
        });
        
        // Hide sidebar when clicking on nav links on mobile
        $('.nav-sidebar .nav-link').on('click', function() {
            if (isMobile()) {
                sidebar.removeClass('show');
                overlay.removeClass('show');
                $('body').removeClass('sidebar-open');
            }
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            if (!isMobile()) {
                sidebar.removeClass('show');
                overlay.removeClass('show');
                $('body').removeClass('sidebar-open');
            }
        });
    }
    
    // ====== RESPONSIVE TABLE HANDLING ======
    
    function initResponsiveTables() {
        // Add responsive wrapper to tables
        $('.table').each(function() {
            if (!$(this).parent().hasClass('table-responsive')) {
                $(this).wrap('<div class="table-responsive"></div>');
            }
        });
        
        // Handle table overflow on mobile
        $('.table-responsive').on('scroll', function() {
            $(this).addClass('scrolling');
        });
    }
    
    // ====== RESPONSIVE CHART HANDLING ======
    
    function initResponsiveCharts() {
        // Resize charts on window resize
        $(window).on('resize', function() {
            if (typeof Chart !== 'undefined') {
                Chart.instances.forEach(function(chart) {
                    chart.resize();
                });
            }
        });
    }
    
    // ====== MOBILE FORM OPTIMIZATIONS ======
    
    function initMobileForms() {
        // Prevent zoom on input focus for iOS
        $('input, select, textarea').on('focus', function() {
            if (isMobile()) {
                $(this).css('font-size', '16px');
            }
        });
        
        // Reset font size on blur
        $('input, select, textarea').on('blur', function() {
            if (isMobile()) {
                $(this).css('font-size', '');
            }
        });
    }
    
    // ====== RESPONSIVE MODAL HANDLING ======
    
    function initResponsiveModals() {
        // Adjust modal size for mobile
        $('.modal').on('show.bs.modal', function() {
            if (isMobile()) {
                $(this).find('.modal-dialog').addClass('modal-fullscreen-sm-down');
            }
        });
        
        // Reset modal on hide
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('.modal-dialog').removeClass('modal-fullscreen-sm-down');
        });
    }
    
    // ====== TOUCH DEVICE OPTIMIZATIONS ======
    
    function initTouchOptimizations() {
        // Add touch feedback to buttons
        $('.btn, .nav-link, .card').on('touchstart', function() {
            $(this).addClass('touch-active');
        });
        
        $('.btn, .nav-link, .card').on('touchend touchcancel', function() {
            $(this).removeClass('touch-active');
        });
        
        // Prevent double tap zoom on buttons
        $('.btn, .nav-link').on('touchend', function(e) {
            e.preventDefault();
            $(this).click();
        });
    }
    
    // ====== RESPONSIVE NAVIGATION ======
    
    function initResponsiveNavigation() {
        // Handle dropdown menus on mobile
        $('.dropdown-toggle').on('click', function(e) {
            if (isMobile()) {
                e.preventDefault();
                const dropdown = $(this).next('.dropdown-menu');
                dropdown.toggleClass('show');
            }
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    }
    
    // ====== RESPONSIVE UTILITIES ======
    
    function initResponsiveUtilities() {
        // Add responsive classes based on screen size
        function updateResponsiveClasses() {
            const width = window.innerWidth;
            
            $('body').removeClass('screen-xs screen-sm screen-md screen-lg screen-xl');
            
            if (width < 576) {
                $('body').addClass('screen-xs');
            } else if (width < 768) {
                $('body').addClass('screen-sm');
            } else if (width < 992) {
                $('body').addClass('screen-md');
            } else if (width < 1200) {
                $('body').addClass('screen-lg');
            } else {
                $('body').addClass('screen-xl');
            }
        }
        
        updateResponsiveClasses();
        $(window).on('resize', updateResponsiveClasses);
    }
    
    // ====== PERFORMANCE OPTIMIZATIONS ======
    
    function initPerformanceOptimizations() {
        // Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Debounce resize events
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Trigger custom resize event
                $(window).trigger('debounced-resize');
            }, 250);
        });
    }
    
    // ====== ACCESSIBILITY IMPROVEMENTS ======
    
    function initAccessibility() {
        // Add keyboard navigation for sidebar
        $('#sidebarToggle').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Add focus management for modals
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('[autofocus]').focus();
        });
        
        // Add skip links for keyboard users
        if (!$('#skip-link').length) {
            $('body').prepend(`
                <a href="#main-content" id="skip-link" class="sr-only sr-only-focusable">
                    Skip to main content
                </a>
            `);
        }
    }
    
    // ====== THEME AND PREFERENCES ======
    
    function initThemePreferences() {
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('admin-theme');
        if (savedTheme) {
            $('body').attr('data-theme', savedTheme);
        }
        
        // Check for reduced motion preference
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            $('body').addClass('reduced-motion');
        }
        
        // Check for high contrast preference
        if (window.matchMedia('(prefers-contrast: high)').matches) {
            $('body').addClass('high-contrast');
        }
    }
    
    // ====== ERROR HANDLING ======
    
    function initErrorHandling() {
        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });
        
        // AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, error) {
            console.error('AJAX error:', error);
            
            // Show user-friendly error message
            if (xhr.status === 500) {
                showNotification('Terjadi kesalahan server. Silakan coba lagi.', 'error');
            } else if (xhr.status === 404) {
                showNotification('Halaman tidak ditemukan.', 'warning');
            }
        });
    }
    
    // ====== NOTIFICATION SYSTEM ======
    
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} mr-2"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        // Add notification container if it doesn't exist
        if (!$('#notification-container').length) {
            $('body').append('<div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        $('#notification-container').append(notification);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // ====== INITIALIZATION ======
    
    // Initialize all functions
    initSidebar();
    initResponsiveTables();
    initResponsiveCharts();
    initMobileForms();
    initResponsiveModals();
    initTouchOptimizations();
    initResponsiveNavigation();
    initResponsiveUtilities();
    initPerformanceOptimizations();
    initAccessibility();
    initThemePreferences();
    initErrorHandling();
    
    // Make functions globally available
    window.showNotification = showNotification;
    window.isMobile = isMobile;
    
    // Trigger custom event when everything is ready
    $(document).trigger('admin-ready');
    
    console.log('Admin panel responsive features initialized');
});

// ====== ALPINE.JS INTEGRATION ======

// If Alpine.js is loaded, add responsive data
if (typeof Alpine !== 'undefined') {
    Alpine.data('responsiveAdmin', () => ({
        isMobile: false,
        sidebarOpen: false,
        
        init() {
            this.checkScreenSize();
            window.addEventListener('resize', () => this.checkScreenSize());
        },
        
        checkScreenSize() {
            this.isMobile = window.innerWidth <= 991.98;
        },
        
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }));
} 