/**
 * Meja Module JavaScript
 * Handles DataTables, modals, and CRUD operations for the Meja management system
 */

$(document).ready(function() {
    // Initialize DataTable
    initializeDataTable();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
        
    // Event listener for create button
    $(document).on('click', '.btn-create-meja', function() {
        openCreateModal();
    });
    
    // Event listener for edit button
    $(document).on('click', '.btn-edit-meja', function() {
        const mejaId = $(this).data('meja-id');
        openEditModal(mejaId);
    });
    
    // Event listener for delete button
    $(document).on('click', '.btn-delete-meja', function() {
        const mejaId = $(this).data('meja-id');
        const mejaName = $(this).data('meja-name');
        deleteMeja(mejaId, mejaName);
    });
});

/**
 * Initialize DataTable with custom configuration
 */
function initializeDataTable() {
    $('#mejaTable').DataTable({
        responsive: false,
        scrollX: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: -1, // Last column (Actions)
                orderable: false,
                searchable: false
            }
        ],
        drawCallback: function(settings) {
            // Reinitialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

/**
 * Open create modal and load form
 */
function openCreateModal() {
    // Load create form
    $.ajax({
        url: window.createModalUrl,
        method: 'GET',
        success: function(response) {
            $('#addModalBody').html(response);
            $('#addModal').modal('show');
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat form.';
            
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMessage,
                confirmButtonColor: '#e74a3b'
            });
        }
    });
}

/**
 * Open edit modal and load form
 * @param {number} id - The meja ID
 */
function openEditModal(id) {
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID meja tidak ditemukan.',
        });
        return;
    }

    // Load edit form
    $.ajax({
        url: `/meja/${id}/edit-modal`,
        type: 'GET',
        success: function(response) {
            // Masukkan form hasil dari controller ke dalam modal
            $('#editModalBody').html(response);

            // Tampilkan modal edit
            $('#editModal').modal('show');
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: xhr.responseJSON?.message || 'Tidak dapat memuat form edit.',
            });
        }
    });
}


/**
 * Delete meja with confirmation
 * @param {number} id - The meja ID
 * @param {string} nama - The meja name
 */
function deleteMeja(id, nama) {
    Swal.fire({
        title: 'Yakin hapus meja "' + nama + '"?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send delete request
            $.ajax({
                url: '/meja/' + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data meja berhasil dihapus!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage
                    });
                }
            });
        }
    });
}

/**
 * Refresh DataTable
 */
function refreshTable() {
    Swal.fire({
        title: 'Memperbarui Data...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        location.reload();
    }, 500);
}

/**
 * Search table
 * @param {string} query - Search query
 */
function searchTable(query) {
    $('#mejaTable').DataTable().search(query).draw();
    
    if (query.trim() !== '') {
        showInfo(`Mencari: "${query}"`);
    }
}

/**
 * Clear table search
 */
function clearSearch() {
    $('#mejaTable').DataTable().search('').draw();
    showInfo('Pencarian telah dibersihkan');
}

/**
 * Show loading overlay
 */
function showLoading() {
    $('body').append('<div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;"><div class="text-center text-white"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading...</p></div></div>');
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('#loading-overlay').remove();
}

/**
 * Show success notification
 * @param {string} message - Success message
 */
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        showConfirmButton: false,
        timer: 2000,
        toast: true,
        position: 'top-end',
        timerProgressBar: true
    });
}

/**
 * Show error notification
 * @param {string} message - Error message
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        position: 'top-end',
        timerProgressBar: true
    });
}

/**
 * Show warning notification
 * @param {string} message - Warning message
 */
function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: message,
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        position: 'top-end',
        timerProgressBar: true
    });
}

/**
 * Show info notification
 * @param {string} message - Info message
 */
function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Info!',
        text: message,
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        position: 'top-end',
        timerProgressBar: true
    });
}

/**
 * Show confirmation dialog
 * @param {string} title - Dialog title
 * @param {string} text - Dialog text
 * @param {string} confirmText - Confirm button text
 * @param {string} cancelText - Cancel button text
 * @returns {Promise} - Promise that resolves to the result
 */
function showConfirmation(title, text, confirmText = 'Ya', cancelText = 'Tidak') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4e73df',
        cancelButtonColor: '#858796',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
}

/**
 * Show input dialog
 * @param {string} title - Dialog title
 * @param {string} text - Dialog text
 * @param {string} inputType - Input type (text, email, etc.)
 * @param {string} placeholder - Input placeholder
 * @returns {Promise} - Promise that resolves to the input value
 */
function showInputDialog(title, text, inputType = 'text', placeholder = '') {
    return Swal.fire({
        title: title,
        text: text,
        input: inputType,
        inputPlaceholder: placeholder,
        showCancelButton: true,
        confirmButtonColor: '#4e73df',
        cancelButtonColor: '#858796',
        confirmButtonText: 'OK',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Anda harus mengisi field ini!';
            }
        }
    });
}

/**
 * Format date to Indonesian format
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}

/**
 * Format currency to Indonesian format
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

/**
 * Validate form fields
 * @param {Object} formData - Form data object
 * @returns {Object} Validation result
 */
function validateForm(formData) {
    const errors = {};
    
    if (!formData.nama_meja || formData.nama_meja.trim() === '') {
        errors.nama_meja = 'Nama meja harus diisi';
    }
    
    if (!formData.status || formData.status === '') {
        errors.status = 'Status harus dipilih';
    }
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors: errors
    };
}

/**
 * Clear form validation errors
 * @param {string} formId - Form ID
 */
function clearValidationErrors(formId) {
    $(`#${formId} .is-invalid`).removeClass('is-invalid');
    $(`#${formId} .invalid-feedback`).remove();
}

/**
 * Show form validation errors
 * @param {string} formId - Form ID
 * @param {Object} errors - Validation errors
 */
function showValidationErrors(formId, errors) {
    clearValidationErrors(formId);
    
    Object.keys(errors).forEach(field => {
        const fieldElement = $(`#${formId} [name="${field}"]`);
        fieldElement.addClass('is-invalid');
        
        const errorDiv = $(`<div class="invalid-feedback">${errors[field]}</div>`);
        fieldElement.after(errorDiv);
    });
}

// Global event handlers
$(document).on('click', '[data-toggle="tooltip"]', function() {
    $(this).tooltip('hide');
});

// Handle modal events
$(document).on('hidden.bs.modal', '.modal', function() {
    // Clear any remaining validation errors
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').remove();
});

// Handle form submission errors globally
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 422) {
        // Validation errors
        const errors = xhr.responseJSON.errors;
        const formId = settings.url.includes('store') ? 'createMejaForm' : 'updateMejaForm';
        showValidationErrors(formId, errors);
    } else if (xhr.status === 500) {
        showError('Terjadi kesalahan server. Silakan coba lagi.');
    } else if (xhr.status === 404) {
        showError('Data tidak ditemukan.');
    }
});

// Export functions to global scope
window.mejaModule = {
    openCreateModal,
    openEditModal,
    deleteMeja,
    refreshTable,
    searchTable,
    clearSearch,
    showLoading,
    hideLoading,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirmation,
    showInputDialog,
    formatDate,
    formatCurrency,
    validateForm,
    clearValidationErrors,
    showValidationErrors
};