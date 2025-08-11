/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 * 
 */

"use strict";

$(document).ready(function() {
    // Sidebar toggle functionality
    $('#sidebar-toggle').on('click', function(e) {
        e.preventDefault();
        
        if (window.innerWidth >= 992) {
            // Desktop behavior - collapse/expand sidebar
            $('.main-sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
            $('.main-navbar').toggleClass('expanded');
            $('.main-footer').toggleClass('expanded');
        } else {
            // Mobile behavior - show/hide sidebar
            $('.main-sidebar').toggleClass('show');
            $('#sidebar-backdrop').toggleClass('show');
        }
    });

    // Close sidebar when clicking backdrop (mobile)
    $('#sidebar-backdrop').on('click', function() {
        $('.main-sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // Handle window resize
    $(window).on('resize', function() {
        if (window.innerWidth >= 992) {
            // Desktop: hide backdrop and show sidebar
            $('#sidebar-backdrop').removeClass('show');
            $('.main-sidebar').removeClass('show');
        } else {
            // Mobile: collapse sidebar and remove expanded classes
            $('.main-sidebar').removeClass('collapsed');
            $('.main-content').removeClass('expanded');
            $('.main-navbar').removeClass('expanded');
            $('.main-footer').removeClass('expanded');
        }
    });

    // Initialize sidebar state based on screen size
    if (window.innerWidth < 992) {
        $('.main-sidebar').removeClass('collapsed');
        $('.main-content').removeClass('expanded');
        $('.main-navbar').removeClass('expanded');
        $('.main-footer').removeClass('expanded');
    }

    // Sidebar dropdown functionality
    $('.sidebar-menu .nav-link.has-dropdown').on('click', function(e) {
        e.preventDefault();
        
        const $this = $(this);
        const $parent = $this.parent();
        const $dropdown = $parent.find('.dropdown-menu');
        
        // Close other dropdowns
        $('.sidebar-menu .dropdown-menu').not($dropdown).slideUp(300);
        $('.sidebar-menu .nav-link.has-dropdown').not($this).removeClass('active');
        
        // Toggle current dropdown
        $dropdown.slideToggle(300);
        $this.toggleClass('active');
        
        // Update aria-expanded
        const isExpanded = $dropdown.is(':visible');
        $this.attr('aria-expanded', isExpanded);
    });

    // Initialize NiceScroll for sidebar
    if (typeof $.fn.niceScroll !== 'undefined') {
        $('.main-sidebar').niceScroll({
            cursorcolor: '#6777ef',
            cursorwidth: '6px',
            cursorborder: 'none',
            cursorborderradius: '3px',
            scrollspeed: 60,
            mousescrollstep: 40,
            autohidemode: true,
            horizrailenabled: false
        });
    }

    // Close dropdowns when clicking outside (for collapsed sidebar)
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.main-sidebar').length) {
            $('.sidebar-menu .dropdown-menu').slideUp(300);
            $('.sidebar-menu .nav-link.has-dropdown').removeClass('active');
        }
    });

    // Handle hover for collapsed sidebar dropdowns
    $('.main-sidebar.collapsed .sidebar-menu .nav-link.has-dropdown').hover(
        function() {
            const $this = $(this);
            const $parent = $this.parent();
            const $dropdown = $parent.find('.dropdown-menu');
            
            // Show dropdown
            $dropdown.addClass('show');
        },
        function() {
            const $this = $(this);
            const $parent = $this.parent();
            const $dropdown = $parent.find('.dropdown-menu');
            
            // Hide dropdown after delay
            setTimeout(function() {
                if (!$dropdown.is(':hover')) {
                    $dropdown.removeClass('show');
                }
            }, 200);
        }
    );

    // Handle hover for collapsed sidebar dropdown menu
    $('.main-sidebar.collapsed .sidebar-menu .dropdown-menu').hover(
        function() {
            $(this).addClass('show');
        },
        function() {
            $(this).removeClass('show');
        }
    );

    // Add active class to current page menu item
    $('.sidebar-menu .nav-link').each(function() {
        const href = $(this).attr('href');
        if (href && href !== '#' && window.location.pathname.includes(href)) {
            $(this).addClass('active');
            $(this).parents('.dropdown').addClass('active');
            $(this).parents('.dropdown-menu').show();
        }
    });

    // Smooth scrolling for sidebar
    $('.main-sidebar').on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        const scrollHeight = $(this)[0].scrollHeight;
        const clientHeight = $(this)[0].clientHeight;
        
        // Add shadow to top/bottom when scrolling
        if (scrollTop > 0) {
            $(this).addClass('scrolled-top');
        } else {
            $(this).removeClass('scrolled-top');
        }
        
        if (scrollTop + clientHeight < scrollHeight) {
            $(this).addClass('scrolled-bottom');
        } else {
            $(this).removeClass('scrolled-bottom');
        }
    });

    // Add loading state to sidebar toggle
    $('#sidebar-toggle').on('click', function() {
        const $icon = $(this).find('i');
        const originalClass = $icon.attr('class');
        
        // Add loading animation
        $icon.removeClass().addClass('fas fa-spinner fa-spin');
        
        setTimeout(function() {
            $icon.removeClass().addClass(originalClass);
        }, 300);
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + B to toggle sidebar
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            $('#sidebar-toggle').click();
        }
        
        // Escape to close mobile sidebar
        if (e.key === 'Escape' && window.innerWidth < 992) {
            $('.main-sidebar').removeClass('show');
            $('#sidebar-backdrop').removeClass('show');
        }
    });

    // Add touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        const swipeDistance = touchEndX - touchStartX;
        
        if (Math.abs(swipeDistance) > swipeThreshold) {
            if (swipeDistance > 0 && touchStartX < 50) {
                // Swipe right from left edge - open sidebar
                if (window.innerWidth < 992) {
                    $('.main-sidebar').addClass('show');
                    $('#sidebar-backdrop').addClass('show');
                }
            } else if (swipeDistance < 0 && $('.main-sidebar').hasClass('show')) {
                // Swipe left - close sidebar
                $('.main-sidebar').removeClass('show');
                $('#sidebar-backdrop').removeClass('show');
            }
        }
    }

    // Add CSS classes for better styling
    $('body').addClass('sidebar-enabled');
    
    // Initialize tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    if (typeof $.fn.popover !== 'undefined') {
        $('[data-toggle="popover"]').popover();
    }
});
