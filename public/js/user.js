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
    
    // Configure DataTables based on screen size
    const dataTableConfig = {
        responsive: {
            details: {
                type: 'none', // Menghapus button expand/collapse
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        var data = row.data();
                        return 'Detail: ' + data[2]; // Nama User
                    }
                }),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                    tableClass: 'table table-sm table-bordered'
                })
            }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: isMobile ? 5 : (isTablet ? 10 : 10),
        lengthMenu: isMobile ? [[5, 10, 25], [5, 10, 25]] : [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: -1, // Aksi column
                orderable: false,
                searchable: false,
                responsivePriority: 1,
                className: 'text-center'
            },
            {
                targets: [0], // No column
                responsivePriority: 2,
                className: 'text-center'
            },
            {
                targets: [2], // Nama User column
                responsivePriority: 3
            },
            {
                targets: [3], // Email column
                responsivePriority: 4,
                visible: !isMobile
            },
            {
                targets: [5, 6], // Bagian and Grup columns
                responsivePriority: 5,
                visible: !isMobile && !isTablet
            }
        ],
        initComplete: function() {
            // Initialize tooltips after table is loaded
            $('[data-toggle="tooltip"]').tooltip();
        }
    };
    
    // Configure DOM based on screen size
    if (!isMobile) {
        dataTableConfig.dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                             '<"row"<"col-sm-12"tr>>' +
                             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
    } else {
        // Simplified DOM for mobile
        dataTableConfig.dom = '<"row"<"col-12"f>>' +
                             '<"row"<"col-12"tr>>' +
                             '<"row"<"col-12"i><"col-12"p>>';
    }
    
    // Initialize DataTable
    const userTable = $('#userTable').DataTable(dataTableConfig);
    
    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            userTable.columns.adjust().responsive.recalc();
        }, 250);
    });
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
