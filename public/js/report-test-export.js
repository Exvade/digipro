/**
 * Report Test Export Page JavaScript
 * Handles test export functionality for Excel and PDF formats
 */

$(document).ready(function() {
    // Set default dates (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#start_date').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#end_date').val(today.toISOString().split('T')[0]);
});

/**
 * Test export function
 * @param {string} format - The export format ('excel' or 'pdf')
 */
function testExport(format) {
    // Validate dates first
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    
    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Error Tanggal',
            text: 'Tanggal mulai dan tanggal akhir harus diisi'
        });
        return;
    }
    
    if (startDate > endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Error Tanggal',
            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir'
        });
        return;
    }
    
    // Get form data
    const formData = new FormData($('#testExportForm')[0]);
    
    // Show loading
    Swal.fire({
        title: 'Mengekspor Laporan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Determine the export URL
    let exportUrl;
    if (format === 'excel') {
        exportUrl = window.exportExcelUrl || '/report/export/excel';
    } else if (format === 'pdf') {
        exportUrl = window.exportPdfUrl || '/report/export/pdf';
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Format ekspor tidak valid'
        });
        return;
    }
    
    // Create a temporary form to submit the export request
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = exportUrl;
    tempForm.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
    tempForm.appendChild(csrfToken);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        if (value) { // Only add non-empty values
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }
    }
    
    // Submit the form
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
    
    // Close loading after a short delay
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: `Test export ${format.toUpperCase()} berhasil dilakukan`,
            showConfirmButton: false,
            timer: 1500
        });
    }, 1000);
} 