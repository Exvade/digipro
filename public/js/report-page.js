// Set report type for JavaScript
window.reportType = window.reportType || '';
window.reportStoreUrl = window.reportStoreUrl || '';
window.exportExcelUrl = window.exportExcelUrl || '';
window.exportPdfUrl = window.exportPdfUrl || '';
window.grups = window.grups || [];
window.users = window.users || [];

// Function to apply filters (grup and report type)
function applyFilters() {
    const grupId = document.getElementById('filter_grup_id').value;
    const reportTypeSelector = document.getElementById('report_type_selector');
    const reportType = reportTypeSelector.disabled ? window.userDepartment || '' : reportTypeSelector.value;

    const currentUrl = new URL(window.location);

    // Add or update filter parameters
    if (grupId) {
        currentUrl.searchParams.set('filter_grup_id', grupId);
    } else {
        currentUrl.searchParams.delete('filter_grup_id');
    }

    // For leaders, don't set report_type parameter since it's fixed
    if (reportType && window.userRole !== 'leader') {
        currentUrl.searchParams.set('report_type', reportType);
    } else {
        currentUrl.searchParams.delete('report_type');
    }

    // Redirect to the new URL
    window.location.href = currentUrl.toString();
}

// Function to clear all filters
function clearFilters() {
    const currentUrl = new URL(window.location);

    // Remove all filter parameters
    currentUrl.searchParams.delete('filter_grup_id');

    // For leaders, don't clear report_type since it's fixed to their department
    if (window.userRole !== 'leader') {
        currentUrl.searchParams.delete('report_type');
    }

    // Redirect to the new URL
    window.location.href = currentUrl.toString();
}

// Update department_type when report_type changes in general modal
$(document).ready(function() {
    $('#report_type').on('change', function() {
        const selectedReportType = $(this).val();
        $('input[name="department_type"]').val(selectedReportType);
    });
}); 