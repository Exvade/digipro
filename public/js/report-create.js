/**
 * Report Create Page JavaScript
 * Handles camera functionality, form submission, and export features
 */

$(document).ready(function() {
    let stream = null;
    let capturedImage = null;

    // Initialize camera
    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                } 
            });
            const video = document.getElementById('camera');
            video.srcObject = stream;
        } catch (err) {
            console.error('Error accessing camera:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error Kamera',
                text: 'Tidak dapat mengakses kamera. Pastikan browser mengizinkan akses kamera.'
            });
        }
    }

    // Capture photo
    $('#captureBtn').click(function() {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        
        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert to base64
        capturedImage = canvas.toDataURL('image/jpeg', 0.8);
        
        // Show preview
        $('#capturedPhoto').attr('src', capturedImage);
        $('#photoData').val(capturedImage);
        $('#photoPreview').show();
        $('#retakeBtn').show();
        $('#captureBtn').hide();
        
        // Stop camera stream
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    // Retake photo
    $('#retakeBtn').click(function() {
        $('#photoPreview').hide();
        $('#retakeBtn').hide();
        $('#captureBtn').show();
        $('#photoData').val('');
        capturedImage = null;
        
        // Restart camera
        initCamera();
    });

    // Form submission
    $('#createReportForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate dates
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            Swal.fire({
                icon: 'error',
                title: 'Error Tanggal',
                text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir'
            });
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Membuat Laporan...');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: window.reportStoreUrl || '/report',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Laporan berhasil dibuat',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '/report';
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat membuat laporan';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });

    // Initialize camera when page loads
    initCamera();

    // Clean up camera when leaving page
    $(window).on('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
});

/**
 * Export report function
 * @param {string} format - The export format ('excel' or 'pdf')
 */
function exportReport(format) {
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
    const formData = new FormData($('#createReportForm')[0]);
    
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
        if (value && key !== 'photo_data') { // Exclude photo data for export
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
            text: `Laporan berhasil diekspor ke format ${format.toUpperCase()}`,
            showConfirmButton: false,
            timer: 1500
        });
    }, 1000);
} 