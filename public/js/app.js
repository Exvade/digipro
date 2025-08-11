// ====== PROFESSIONAL ADMIN PANEL JAVASCRIPT ======

$(document).ready(function() {
    
    // ====== DEVICE DETECTION ======
    
    function isMobile() {
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        const screenWidth = window.innerWidth;
        const isIPad = /iPad|Macintosh/.test(navigator.userAgent) && isTouchDevice;
        
        return isIPad || screenWidth <= 1024 || (isTouchDevice && screenWidth <= 1200);
    }
    
    function isIPad() {
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        return /iPad|Macintosh/.test(navigator.userAgent) && isTouchDevice;
    }
    
    // ====== SIDEBAR MANAGEMENT ======
    
    function initSidebar() {
        const sidebar = $('#mainSidebar');
        const overlay = $('#sidebarOverlay');
        const toggleBtn = $('#sidebarToggle');
        
        // Disable AdminLTE's default behavior on mobile/tablet
        if (isMobile()) {
            toggleBtn.removeAttr('data-widget');
        }
        
        // Sidebar toggle functionality
        toggleBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isMobile()) {
                const isOpen = sidebar.hasClass('show');
                
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            } else {
                // Desktop behavior
                if (typeof $.fn.pushMenu !== 'undefined') {
                    $('body').toggleClass('sidebar-collapse');
                } else {
                    $('body').toggleClass('sidebar-collapse');
                }
            }
        });
        
        // Close sidebar when overlay is clicked
        overlay.on('click', function() {
            if (isMobile()) {
                closeSidebar();
            }
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            if (!isMobile()) {
                closeSidebar();
                toggleBtn.attr('data-widget', 'pushmenu');
            } else {
                toggleBtn.removeAttr('data-widget');
            }
        });
        
        // Initialize based on current screen size
        if (isMobile()) {
            toggleBtn.removeAttr('data-widget');
        } else {
            toggleBtn.attr('data-widget', 'pushmenu');
            if (typeof $.fn.pushMenu !== 'undefined' && !toggleBtn.data('pushmenu')) {
                toggleBtn.pushMenu();
            }
        }
    }
    
    function openSidebar() {
        const sidebar = $('#mainSidebar');
        const overlay = $('#sidebarOverlay');
        
        sidebar.addClass('show');
        overlay.addClass('show');
        $('body').addClass('sidebar-open');
    }
    
    function closeSidebar() {
        const sidebar = $('#mainSidebar');
        const overlay = $('#sidebarOverlay');
        
        sidebar.removeClass('show');
        overlay.removeClass('show');
        $('body').removeClass('sidebar-open');
    }
    
    // ====== NAVIGATION HANDLING ======
    
    function initNavigation() {
        // Handle dropdown toggles
        $('.nav-sidebar .nav-item > .nav-link').on('click', function(e) {
            const $this = $(this);
            const $parent = $this.parent();
            const $treeview = $parent.find('.nav-treeview');
            
            if ($treeview.length) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other dropdowns
                $('.nav-sidebar .nav-treeview').not($treeview).removeClass('show');
                $('.nav-sidebar .nav-item > .nav-link').not($this).attr('aria-expanded', 'false');
                $('.nav-sidebar .nav-item').not($parent).removeClass('menu-open');
                
                // Toggle current dropdown
                $treeview.toggleClass('show');
                const isExpanded = $treeview.hasClass('show');
                $this.attr('aria-expanded', isExpanded);
                
                if (isExpanded) {
                    $parent.addClass('menu-open');
                } else {
                    $parent.removeClass('menu-open');
                }
            }
        });
        
        // Handle navigation links
        $(document).on('click', '.nav-sidebar .nav-link[href]', function(e) {
            const $this = $(this);
            const href = $this.attr('href');
            const $parent = $this.parent();
            const $treeview = $parent.find('.nav-treeview');
            
            // Skip if it's a dropdown parent or not a real link
            if ($treeview.length || !href || href === '#' || href === 'javascript:void(0)') {
                return;
            }
            
            // Handle mobile navigation
            if (isMobile()) {
                handleMobileNavigation($this, href);
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        // Handle submenu links
        $(document).on('click', '.nav-sidebar .nav-treeview .nav-link[href]', function(e) {
            const $this = $(this);
            const href = $this.attr('href');
            
            if (!href || href === '#' || href === 'javascript:void(0)') {
                return;
            }
            
            if (isMobile()) {
                handleMobileNavigation($this, href);
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        // Auto-expand dropdown if current page is in it
        $('.nav-sidebar .nav-item').each(function() {
            const $item = $(this);
            const $treeview = $item.find('.nav-treeview');
            const $activeLink = $treeview.find('.nav-link.active');
            
            if ($activeLink.length) {
                $treeview.addClass('show');
                $item.find('> .nav-link').attr('aria-expanded', 'true');
                $item.addClass('menu-open');
            }
        });
    }
    
    function handleMobileNavigation($link, href) {
        if (!href || href === '#' || href === 'javascript:void(0)') {
            return false;
        }
        
        // Add loading state
        $link.addClass('navigating');
        
        // Close sidebar
        closeSidebar();
        
        // Navigate after a short delay
        setTimeout(function() {
            window.location.href = href;
        }, 150);
        
        return true;
    }
    
    // ====== RESPONSIVE FEATURES ======
    
    function initResponsiveFeatures() {
        // Add responsive wrapper to tables
        $('.table').each(function() {
            if (!$(this).parent().hasClass('table-responsive')) {
                $(this).wrap('<div class="table-responsive"></div>');
            }
        });
        
        // Resize charts on window resize
        $(window).on('resize', function() {
            if (typeof Chart !== 'undefined') {
                Chart.instances.forEach(function(chart) {
                    chart.resize();
                });
            }
        });
        
        // Prevent zoom on input focus for iOS
        $('input, select, textarea').on('focus', function() {
            if (isMobile()) {
                $(this).css('font-size', '16px');
            }
        });
        
        $('input, select, textarea').on('blur', function() {
            if (isMobile()) {
                $(this).css('font-size', '');
            }
        });
        
        // Adjust modal size for mobile/tablet
        $('.modal').on('show.bs.modal', function() {
            if (isMobile()) {
                $(this).find('.modal-dialog').addClass('modal-fullscreen-sm-down');
            }
        });
        
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('.modal-dialog').removeClass('modal-fullscreen-sm-down');
        });
    }
    
    // ====== TOUCH OPTIMIZATIONS ======
    
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
    
    // ====== UTILITIES ======
    
    function initUtilities() {
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
        
        // Debounce resize events
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                $(window).trigger('debounced-resize');
            }, 250);
        });
    }
    
    // ====== ACCESSIBILITY ======
    
    function initAccessibility() {
        // Add keyboard navigation for sidebar
        $('#sidebarToggle').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Add keyboard navigation for dropdowns
        $('.nav-sidebar .nav-item > .nav-link').on('keydown', function(e) {
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
    
    // ====== THEME PREFERENCES ======
    
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
    
    // ====== IPAD SPECIFIC HANDLING ======
    
    function initIPadHandling() {
        if (isIPad()) {
            console.log('iPad detected - applying special navigation handling');
            
            // Force all navigation links to work on iPad
            $(document).on('click', '.nav-sidebar .nav-link[href]', function(e) {
                const $this = $(this);
                const href = $this.attr('href');
                const $parent = $this.parent();
                const $treeview = $parent.find('.nav-treeview');
                
                // Skip if it's a dropdown parent
                if ($treeview.length) {
                    return;
                }
                
                // Skip if it's not a real link
                if (!href || href === '#' || href === 'javascript:void(0)') {
                    return;
                }
                
                // Close sidebar and navigate
                closeSidebar();
                
                // Navigate immediately
                setTimeout(function() {
                    window.location.href = href;
                }, 100);
                
                e.preventDefault();
                e.stopPropagation();
            });
            
            // Handle submenu links on iPad
            $(document).on('click', '.nav-sidebar .nav-treeview .nav-link[href]', function(e) {
                const $this = $(this);
                const href = $this.attr('href');
                
                if (!href || href === '#' || href === 'javascript:void(0)') {
                    return;
                }
                
                // Close sidebar and navigate
                closeSidebar();
                
                // Navigate immediately
                setTimeout(function() {
                    window.location.href = href;
                }, 100);
                
                e.preventDefault();
                e.stopPropagation();
            });
            
            // Ensure touch events work properly on iPad
            $(document).on('touchstart', '.nav-sidebar .nav-link', function(e) {
                $(this).addClass('touch-active');
            });
            
            $(document).on('touchend', '.nav-sidebar .nav-link', function(e) {
                $(this).removeClass('touch-active');
            });
        }
    }
    
    // ====== INITIALIZATION ======
    
    // Initialize all functions
    initSidebar();
    initNavigation();
    initResponsiveFeatures();
    initTouchOptimizations();
    initUtilities();
    initAccessibility();
    initThemePreferences();
    initErrorHandling();
    initIPadHandling();
    
    // Ensure sidebar toggle works immediately
    $(document).ready(function() {
        if (isMobile()) {
            const toggleBtn = $('#sidebarToggle');
            const sidebar = $('#mainSidebar');
            const overlay = $('#sidebarOverlay');
            
            // Remove any existing click handlers
            toggleBtn.off('click');
            
            // Add fresh click handler
            toggleBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = sidebar.hasClass('show');
                
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }
    });
    
    // Make functions globally available
    window.showNotification = showNotification;
    window.isMobile = isMobile;
    window.isIPad = isIPad;
    window.openSidebar = openSidebar;
    window.closeSidebar = closeSidebar;
    
    // Trigger custom event when everything is ready
    $(document).trigger('admin-ready');
    
    console.log('Professional admin panel features initialized');
});

// ====== ALPINE.JS INTEGRATION ======

// If Alpine.js is loaded, add responsive data
if (typeof Alpine !== 'undefined') {
    Alpine.data('responsiveAdmin', () => ({
        isMobile: false,
        isIPad: false,
        sidebarOpen: false,
        
        init() {
            this.checkScreenSize();
            window.addEventListener('resize', () => this.checkScreenSize());
        },
        
        checkScreenSize() {
            this.isMobile = window.innerWidth <= 1024 || ('ontouchstart' in window && window.innerWidth <= 1200);
            this.isIPad = /iPad|Macintosh/.test(navigator.userAgent) && ('ontouchstart' in window || navigator.maxTouchPoints > 0);
        },
        
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }));
} 