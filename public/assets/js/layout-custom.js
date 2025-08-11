document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mainSidebar = document.getElementById('main-sidebar');
    const mainContent = document.getElementById('main-content');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            mainSidebar.classList.toggle('show');
            sidebarBackdrop.classList.toggle('show');
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function() {
            mainSidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
        });
    }

    // Dropdown Menu
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    // Initialize NiceScroll
    if (typeof $.fn.niceScroll !== 'undefined') {
        $("html").niceScroll({
            cursorcolor: '#4e73df',
            cursorwidth: '8px',
            cursorborder: 'none'
        });
    }
}); 