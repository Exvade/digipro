/**
 * User JavaScript
 */

$(document).ready(function() {
    // Check if jQuery and Bootstrap are available
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap modal available:', typeof $.fn.modal !== 'undefined');
    
    // Initialize modals
    if (typeof $.fn.modal !== 'undefined') {
        console.log('Bootstrap modals initialized successfully');
    } else {
        console.error('Bootstrap modals not available!');
    }
    
    // Detect device type
    const isMobile = window.innerWidth < 768;
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 992;
    
    // Configure DataTables
    const dataTableConfig = {
        responsive: false, // Horizontal scrolling on mobile inside table-responsive container
        autoWidth: false,
        pageLength: isMobile ? 10 : 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        language: {
            "emptyTable": "Tidak ada data user yang tersedia",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ user",
            "infoEmpty": "Menampilkan 0 user",
            "infoFiltered": "(disaring dari _MAX_ total user)",
            "lengthMenu": "Tampilkan _MENU_ user",
            "loadingRecords": "Memuat data...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "searchPlaceholder": "Cari user, NIK, nama...",
            "zeroRecords": "Tidak ditemukan user yang sesuai",
            "paginate": {
                "first": '<i class="fas fa-angle-double-left text-xs"></i>',
                "last": '<i class="fas fa-angle-double-right text-xs"></i>',
                "next": '<i class="fas fa-angle-right text-xs"></i>',
                "previous": '<i class="fas fa-angle-left text-xs"></i>'
            }
        },
        dom: "<'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4'<'w-full sm:w-auto'l><'w-full sm:w-auto'f>>" +
             "<'overflow-x-auto custom-scrollbar'tr>" +
             "<'flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800'<'text-xs font-medium text-slate-500 dark:text-slate-400'i><'flex items-center gap-1'p>>",
        columnDefs: [
            {
                targets: -1, // Aksi column
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                targets: [0], // No column
                className: 'text-center'
            }
        ],
        initComplete: function() {
            // Initialize tooltips after table is loaded
            $('[data-toggle="tooltip"]').tooltip();
        }
    };
    
    // Initialize DataTable
    const userTable = $('#userTable').DataTable(dataTableConfig);
    
    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            userTable.columns.adjust();
        }, 250);
    });

    // ==========================================================================
    // KPI Cards: Counter Animation & Interactive Table Filtering
    // ==========================================================================
    initKpiCounters();
    initKpiCardFilters(userTable);

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var isDecimal = $this.data('decimal') === true || $this.data('decimal') === 'true';
            var target = parseFloat($this.data('target')) || 0;
            if (target === 0) {
                $this.text(isDecimal ? '0.00' : '0');
                return;
            }

            var duration = 750;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = start + (target - start) * ease;

                if (isDecimal) {
                    $this.text(currentVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                } else {
                    $this.text(Math.floor(currentVal).toLocaleString());
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    if (isDecimal) {
                        $this.text(target.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    } else {
                        $this.text(target.toLocaleString());
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiCardFilters(table) {
        $('.kpi-card').on('click', function() {
            var $card = $(this);
            var filter = $card.data('filter');
            var isAlreadyActive = $card.hasClass('active');

            // Reset active states
            $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20 ring-emerald-500/20 ring-amber-500/20 ring-sky-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            if (isAlreadyActive && filter !== 'all') {
                // Deselect: return to default 'all'
                $('.kpi-card[data-filter="all"]').addClass('active ring-2 ring-indigo-500/20');
                $('.kpi-card[data-filter="all"] .kpi-active-indicator').removeClass('hidden');
                table.column(7).search('').order([[0, 'asc']]).draw();
                return;
            }

            var ringClass = 'ring-2 ring-indigo-500/20';
            if (filter === 'with-group') ringClass = 'ring-2 ring-emerald-500/20';
            else if (filter === 'no-group') ringClass = 'ring-2 ring-amber-500/20';
            else if (filter === 'groups') ringClass = 'ring-2 ring-sky-500/20';

            $card.addClass('active ' + ringClass);
            $card.find('.kpi-active-indicator').removeClass('hidden');

            if (filter === 'all') {
                table.column(7).search('').order([[0, 'asc']]).draw();
            } else if (filter === 'with-group') {
                table.column(7).search('with-group').order([[0, 'asc']]).draw();
            } else if (filter === 'no-group') {
                table.column(7).search('no-group').order([[0, 'asc']]).draw();
            } else if (filter === 'groups') {
                // Filter to users with group and sort by group name
                table.column(7).search('with-group').order([[7, 'asc']]).draw();
            }
        });
    }
});

// =========================================
// Function to open detail modal (Index Page)
// =========================================
window.openDetailModal = function(userId) {
    // Show modal first
    $('#detailModal').modal('show');
    
    // Load detail content via AJAX
    $.ajax({
        url: '/user/' + userId + '/show-modal',
        method: 'GET',
        beforeSend: function() {
            $('#detailModalBody').html('');
        },
        success: function(response) {
            $('#detailModalBody').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading detail:', error);
            $('#detailModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Terjadi kesalahan saat memuat data user.<br>
                    Error: ${error}<br>
                    Status: ${xhr.status}
                </div>
            `);
        }
    });
};

// =========================================
// Function to open create modal
// =========================================
function openCreateModal() {
    console.log('Opening create modal...');
    
    // Check if Bootstrap modal is available
    if (typeof $.fn.modal === 'undefined') {
        console.error('Bootstrap modal not available, using fallback');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Modal tidak tersedia. Silakan refresh halaman.'
        });
        return;
    }

    // Show modal first
    $('#addModal').modal('show');

    // Load form content via AJAX
    $.ajax({
        url: window.createModalUrl,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function(response) {
            console.log('Form loaded successfully');
            $('#addModalBody').html(response);
            
            // Initialize form elements after loading
            initializeFormElements();
            if (typeof initUserCreateForm === 'function') {
                initUserCreateForm();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading form:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            let errorMessage = 'Terjadi kesalahan saat memuat form.';
            
            if (xhr.status === 404) {
                errorMessage = 'Route tidak ditemukan. Pastikan Anda sudah login.';
            } else if (xhr.status === 401) {
                errorMessage = 'Anda harus login terlebih dahulu.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses ke halaman ini.';
            }
            
            $('#addModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMessage}<br>
                    Error: ${error}<br>
                    Status: ${xhr.status}<br>
                    URL: ${window.createModalUrl}
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// =========================================
// Function to open edit modal
// =========================================
function openEditModal(userId) {
    // Show modal first
    $('#editModal').modal('show');
    
    // Load form content via AJAX
    $.ajax({
        url: '/user/' + userId + '/edit-modal',
        method: 'GET',
        beforeSend: function() {
            $('#editModalBody').html('');
        },
        success: function(response) {
            $('#editModalBody').html(response);
            
            // Initialize form elements after loading
            initializeFormElements();
        },
        error: function(xhr, status, error) {
            console.error('Error loading edit form:', error);
            $('#editModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat data user.<br>
                    Error: ${error}<br>
                    Status: ${xhr.status}
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// =========================================
// Function to initialize form elements
// =========================================
function initializeFormElements() {
    // Initialize select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            dropdownParent: $('.modal'),
            width: '100%'
        });
    }
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize form validation
    if ($.fn.validate) {
        $('form').validate({
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            }
        });
    }
    
    // Handle form submission
    $('form').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).text('Menyimpan...');
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 5000);
    });
}

// =========================================
// Function to delete user
// =========================================
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus user "${userName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'swal2-responsive',
            title: 'swal2-title-responsive',
            content: 'swal2-content-responsive'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create form and submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/user/' + userId;
            
            // Add CSRF token
            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfToken);
            
            // Add method override
            var methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// =========================================
// Handle modal events for better mobile experience
// =========================================
$(document).on('shown.bs.modal', '.modal', function() {
    // Focus on first input in modal
    $(this).find('input:first').focus();
    
    // Adjust modal position on mobile
    if (window.innerWidth < 768) {
        $(this).find('.modal-dialog').css('margin', '0.5rem auto');
    }
});

// =========================================
// Handle touch events for better mobile interaction
// =========================================
$(document).on('touchstart', '.btn', function() {
    $(this).addClass('btn-touch');
});

// =========================================
// Handle touch events for better mobile interaction
// =========================================
$(document).on('touchend touchcancel', '.btn', function() {
    $(this).removeClass('btn-touch');
});

// =========================================
// Add responsive table enhancements
// =========================================
$(document).on('click', '#userTable td', function() {
    if (window.innerWidth < 768) {
        const cell = $(this);
        const text = cell.text().trim();
        
        if (text.length > 20) {
            // Show full text in tooltip or modal for long content
            Swal.fire({
                title: 'Detail',
                text: text,
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        }
    }
});


// =========================================
// Dark Mode Toggle Functionality
// =========================================
$(document).ready(function() {
    const $darkModeSwitch = $('#darkModeSwitch');
    const $darkModeIcon = $('#darkModeIcon');
    const $body = $('body');

    // Check if elements exist
    if ($darkModeSwitch.length === 0) {
        // console.warn('Dark mode elements not found');
        return;
    }

    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'enabled';

    // Apply saved preference on page load
    if (isDarkMode) {
        enableDarkMode();
    }

    // Toggle dark mode on switch change
    $darkModeSwitch.on('change', function() {
        if ($(this).is(':checked')) {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    });

    function enableDarkMode() {
        $body.addClass('dark-mode');
        $darkModeSwitch.prop('checked', true);
        $darkModeIcon.removeClass('fa-moon').addClass('fa-sun');
        localStorage.setItem('darkMode', 'enabled');
    }

    function disableDarkMode() {
        $body.removeClass('dark-mode');
        $darkModeSwitch.prop('checked', false);
        $darkModeIcon.removeClass('fa-sun').addClass('fa-moon');
        localStorage.setItem('darkMode', 'disabled');
    }
});

/* Merged JS */
/**
 * User Create Form JavaScript
 * Handles form submission, validation, and grup filtering
 */

$(document).ready(function() {
    // Initialize user create form functionality
    initUserCreateForm();
});

function initUserCreateForm() {
    const form = $('#addForm');
    
    if (form.length === 0) {
        console.log('User create form not found');
        return;
    }
    
    console.log('Initializing user create form...');
    
    // Form submission handler
    form.off('submit').on('submit', handleFormSubmission);
    
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
                showSuccessMessage(response.message || 'User berhasil ditambahkan.');
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
        $('#addModal').modal('hide');
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
        
        console.log('Bagian changed to:', selectedBagian);
        
        // Reset grup selection
        grupSelect.val('');
        
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
window.UserCreate = {
    initUserCreateForm,
    initGrupFiltering,
    initPasswordValidation,
    filterGrupOptions
}; 
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
