$(document).ready(function() {
    // Sidebar toggle (bisa di semua device)
    $('#sidebar-toggle').on('click', function() {
        if (window.innerWidth >= 992) {
            // Desktop behavior
            $('.main-sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        } else {
            // Mobile behavior
            $('.main-sidebar').toggleClass('show');
            $('#sidebar-backdrop').toggle($('.main-sidebar').hasClass('show'));
        }
    });

    // Tutup sidebar jika klik backdrop (mobile)
    $('#sidebar-backdrop').on('click', function() {
        $('.main-sidebar').removeClass('show');
        $(this).hide();
    });

    // Handle window resize
    $(window).on('resize', function() {
        if (window.innerWidth >= 992) {
            $('#sidebar-backdrop').hide();
            $('.main-sidebar').removeClass('show');
        } else {
            $('.main-sidebar').removeClass('collapsed');
            $('.main-content').removeClass('expanded');
        }
    });

    // Inisialisasi: backdrop selalu hilang
    $('#sidebar-backdrop').hide();

    // Nice scroll
    $('.sidebar-menu').niceScroll({
        cursorcolor: '#4e73df',
        cursorwidth: '5px',
        cursorborder: 'none'
    });

    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            offset: [0, 10]
        });
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
}); 