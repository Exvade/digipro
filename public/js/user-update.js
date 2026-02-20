/**
 * User Update Form JavaScript
 * Handles form submission, validation, and grup filtering
 */

$(document).ready(function () {
    initUserUpdateForm();
});

function initUserUpdateForm() {
    const form = $('#editForm');
    if (form.length === 0) return console.warn('User update form not found');

    console.log('✅ Initializing user update form...');
    form.on('submit', handleFormSubmission);
    form.find('input, select').on('input change', clearValidationErrors);

    initGrupFiltering();
    initPasswordValidation();
}

function handleFormSubmission(e) {
    e.preventDefault();

    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();

    submitBtn.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');

    clearAllValidationErrors(form);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                showSuccessMessage(response.message || 'User berhasil diperbarui.');
            } else {
                showErrorMessage(response.message || 'Terjadi kesalahan saat menyimpan data.');
            }
        },
        error: function (xhr) {
            handleAjaxError(xhr, form);
        },
        complete: function () {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function clearValidationErrors() {
    const input = $(this);
    input.removeClass('is-invalid is-valid');
    input.closest('.form-group').find('.invalid-feedback, .valid-feedback').html('');
}

function clearAllValidationErrors(form) {
    form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
    form.find('.invalid-feedback, .valid-feedback').html('');
}

function handleAjaxError(xhr, form) {
    const errors = xhr.responseJSON?.errors;

    if (errors) {
        Object.keys(errors).forEach(field => {
            const input = form.find(`[name="${field}"]`);
            const feedback = input.closest('.form-group').find('.invalid-feedback');

            input.addClass('is-invalid');
            if (feedback.length) feedback.html(errors[field][0]);
        });
    } else {
        showErrorMessage(xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.');
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
    const currentUserBagian = $('#editForm').data('current-bagian') || '';

    if (!bagianSelect.length || !grupSelect.length) return;

    const originalOptions = grupSelect.find('option').clone();

    bagianSelect.on('change', function () {
        const selectedBagian = $(this).val();

        if (selectedBagian !== currentUserBagian) grupSelect.val('');
        filterGrupOptions(selectedBagian, grupSelect, originalOptions);
    });

    // Initial filter
    const initialBagian = bagianSelect.val();
    if (initialBagian) filterGrupOptions(initialBagian, grupSelect, originalOptions);
}

function filterGrupOptions(bagian, grupSelect, originalOptions) {
    grupSelect.find('option:not(:first)').remove();

    if (!bagian) {
        originalOptions.each(function () {
            if ($(this).val() !== '') grupSelect.append($(this).clone());
        });
        return;
    }

    originalOptions.each(function () {
        const option = $(this);
        const grupBagian = option.data('bagian');
        const optionText = option.text().toLowerCase();

        if (option.val() === '') return;
        if ((grupBagian && grupBagian.toLowerCase() === bagian.toLowerCase()) ||
            optionText.includes(bagian.toLowerCase())) {
            grupSelect.append(option.clone());
        }
    });
}

function initPasswordValidation() {
    const passwordField = $('#password');
    const confirmField = $('#password_confirmation');

    if (!passwordField.length || !confirmField.length) return;

    confirmField.on('input', function () {
        const password = passwordField.val();
        const confirm = $(this).val();
        const feedback = $(this).closest('.form-group').find('.invalid-feedback');

        if (confirm && password !== confirm) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            feedback.html('Password tidak cocok');
        } else if (confirm) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            feedback.html('');
        } else {
            $(this).removeClass('is-invalid is-valid');
            feedback.html('');
        }
    });

    passwordField.on('input', function () {
        confirmField.removeClass('is-invalid is-valid');
        confirmField.closest('.form-group').find('.invalid-feedback').html('');
    });
}

// Optional: Expose functions globally for debugging
window.UserUpdate = { initUserUpdateForm, filterGrupOptions };
