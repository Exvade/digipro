/**
 * User Update Form JavaScript
 * Handles form submission, validation, and grup filtering
 */

$(document).ready(function() {
    // Initialize user update form functionality
    initUserUpdateForm();
});

function initUserUpdateForm() {
    const form = $('#editForm');
    
    if (form.length === 0) {
        console.log('User update form not found');
        return;
    }
    
    console.log('Initializing user update form...');
    
    // Form submission handler
    form.on('submit', handleFormSubmission);
    
    // Clear validation errors when input changes
    form.find('input, select').on('input change', clearValidationErrors);
    
    // Initialize grup filtering
    initGrupFiltering();
    
    // Initialize password confirmation validation
    initPasswordValidation();
}

function handleFormSubmission(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Disable submit button and show loading
    submitBtn.prop('disabled', true)
             .html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
    
    // Clear previous errors
    clearAllValidationErrors(form);
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccessMessage(response.message || 'User berhasil diperbarui.');
            } else {
                showErrorMessage(response.message || 'Terjadi kesalahan saat menyimpan data.');
            }
        },
        error: function(xhr) {
            handleAjaxError(xhr, form);
        },
        complete: function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function clearValidationErrors() {
    const input = $(this);
    input.removeClass('is-invalid is-valid');
    input.siblings('.invalid-feedback, .valid-feedback').html('');
}

function clearAllValidationErrors(form) {
    form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
    form.find('.invalid-feedback, .valid-feedback').html('');
}

function handleAjaxError(xhr, form) {
    const errors = xhr.responseJSON?.errors;
    
    if (errors) {
        // Show validation errors
        Object.keys(errors).forEach(field => {
            const input = form.find(`[name="${field}"]`);
            const feedback = input.siblings('.invalid-feedback');
            
            input.addClass('is-invalid');
            feedback.html(errors[field][0]);
        });
    } else {
        // Show general error
        const errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.';
        showErrorMessage(errorMessage);
    }
}

function showSuccessMessage(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        // Close modal and reload page
        $('#editModal').modal('hide');
        location.reload();
    });
}

function showErrorMessage(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message
    });
}

function initGrupFiltering() {
    const bagianSelect = $('#bagian');
    const grupSelect = $('#grup_id');
    
    if (bagianSelect.length === 0 || grupSelect.length === 0) {
        console.log('Bagian or grup select not found');
        return;
    }
    
    console.log('Initializing grup filtering...');
    
    // Store original options for reset
    const originalOptions = grupSelect.find('option').clone();
    
    // Handle bagian change
    bagianSelect.on('change', function() {
        const selectedBagian = $(this).val();
        const currentUserBagian = $(this).data('current-bagian') || '';
        
        console.log('Bagian changed to:', selectedBagian);
        console.log('Current user bagian:', currentUserBagian);
        
        // Reset grup selection if bagian changed (except for current user's bagian)
        if (selectedBagian !== currentUserBagian) {
            grupSelect.val('');
        }
        
        // Filter grup options
        filterGrupOptions(selectedBagian, grupSelect, originalOptions);
    });
    
    // Initial filter on page load
    const initialBagian = bagianSelect.val();
    if (initialBagian) {
        filterGrupOptions(initialBagian, grupSelect, originalOptions);
    }
}

function filterGrupOptions(bagian, grupSelect, originalOptions) {
    console.log('Filtering grup options for bagian:', bagian);
    
    // Clear current options except the first one
    grupSelect.find('option:not(:first)').remove();
    
    if (!bagian) {
        // If no bagian selected, show all options
        originalOptions.each(function() {
            if ($(this).val() !== '') {
                grupSelect.append($(this).clone());
            }
        });
        return;
    }
    
    // Filter options based on bagian
    originalOptions.each(function() {
        const option = $(this);
        const optionValue = option.val();
        
        // Always show the first option (placeholder)
        if (optionValue === '') {
            return;
        }
        
        // Get the grup bagian from data attribute or try to extract from text
        const grupBagian = option.data('bagian');
        
        if (grupBagian) {
            // If we have the bagian data attribute, use it for filtering
            if (grupBagian.toLowerCase() === bagian.toLowerCase()) {
                grupSelect.append(option.clone());
            }
        } else {
            // Fallback: try to extract bagian from option text
            const optionText = option.text().toLowerCase();
            if (optionText.includes(bagian.toLowerCase())) {
                grupSelect.append(option.clone());
            }
        }
    });
    
    console.log('Filtered options:', grupSelect.find('option').length - 1);
}

function initPasswordValidation() {
    const passwordField = $('#password');
    const confirmField = $('#password_confirmation');
    
    if (passwordField.length === 0 || confirmField.length === 0) {
        return;
    }
    
    // Real-time password confirmation validation
    confirmField.on('input', function() {
        const password = passwordField.val();
        const confirm = $(this).val();
        
        if (confirm && password !== confirm) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').html('Password tidak cocok');
        } else if (confirm) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this).siblings('.invalid-feedback').html('');
            $(this).siblings('.valid-feedback').html('Password cocok');
        } else {
            $(this).removeClass('is-invalid is-valid');
            $(this).siblings('.invalid-feedback, .valid-feedback').html('');
        }
    });
    
    // Clear validation when password field changes
    passwordField.on('input', function() {
        confirmField.removeClass('is-invalid is-valid');
        confirmField.siblings('.invalid-feedback, .valid-feedback').html('');
    });
}

// Export functions for global access if needed
window.UserUpdate = {
    initUserUpdateForm,
    initGrupFiltering,
    initPasswordValidation,
    filterGrupOptions
}; 