$(document).ready(function() {
    // Read configuration from data attributes
    const configElement = $('#report-config');
    if (configElement.length) {
        window.reportType = configElement.data('report-type');
        window.reportStoreUrl = configElement.data('report-store-url');
        window.exportExcelUrl = configElement.data('export-excel-url');
        window.exportPdfUrl = configElement.data('export-pdf-url');
        window.grups = configElement.data('grups');
        window.users = configElement.data('users');
        window.userDepartment = configElement.data('user-department');
        window.userRole = configElement.data('user-role');
    }

    // Tab Filter Bagian (All, Inject, Assy) - Report Page
    $('#bagianTabsReport .nav-link').on('click', function(e) {
        e.preventDefault();
        const bagian = $(this).data('bagian');
        
        // Update URL with report_type parameter
        const currentUrl = new URL(window.location);
        
        if (bagian === 'general') {
            currentUrl.searchParams.delete('report_type');
        } else {
            currentUrl.searchParams.set('report_type', bagian);
        }
        
        // Preserve grup filter if exists
        const grupFilter = currentUrl.searchParams.get('filter_grup_id');
        if (grupFilter) {
            currentUrl.searchParams.set('filter_grup_id', grupFilter);
        }
        
        // Redirect to new URL
        window.location.href = currentUrl.toString();
    });
});
