$(document).ready(function() {
    // Initialize DataTable with basic configuration
    $('#mesinTable').DataTable({
        responsive: false,
        dom: 'frtip',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']]
    });

    // Event listener for create button
    $(document).on('click', '.btn-create-mesin', function() {
        openCreateModal();
    });

    // Event listener for edit button
    $(document).on('click', '.btn-edit-mesin', function() {
        const mesinId = $(this).data('mesin-id');
        openEditModal(mesinId);
    });

    // Event listener for delete button
    $(document).on('click', '.btn-delete-mesin', function() {
        const mesinId = $(this).data('mesin-id');
        const mesinName = $(this).data('mesin-name');
        deleteMesin(mesinId, mesinName);
    });
});

// Function to open create modal
function openCreateModal() {
    $('#addModal').modal('show');
    
    $.ajax({
        url: window.createModalUrl,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#addModalBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat form...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#addModalBody').html(response);
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat form.';
            
            if (xhr.status === 404) {
                errorMessage = 'Route tidak ditemukan.';
            } else if (xhr.status === 401) {
                errorMessage = 'Anda harus login terlebih dahulu.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses ke halaman ini.';
            }
            
            $('#addModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to open edit modal
function openEditModal(mesinId) {
    $('#editModal').modal('show');
    
    $.ajax({
        url: '/mesin/' + mesinId + '/edit',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#editModalBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#editModalBody').html(response);
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat data.';
            
            if (xhr.status === 404) {
                errorMessage = 'Data mesin tidak ditemukan.';
            } else if (xhr.status === 401) {
                errorMessage = 'Anda harus login terlebih dahulu.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses ke data ini.';
            }
            
            $('#editModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to delete mesin
function deleteMesin(mesinId, mesinName) {
    Swal.fire({
        title: 'Yakin Hapus?',
        html: `Data mesin <b>"${mesinName}"</b> akan dihapus secara permanen!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
        showClass: {
            popup: 'animate__animated animate__zoomIn'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/mesin/' + mesinId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Data mesin berhasil dihapus.',
                            icon: 'success',
                            timer: 200, 
                            showConfirmButton: false,
                            showClass: { popup: 'animate__animated animate__fadeInUp' },
                            hideClass: { popup: 'animate__animated animate__fadeOutDown' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                    if (xhr.status === 404) {
                        errorMessage = 'Data mesin tidak ditemukan.';
                    } else if (xhr.status === 401) {
                        errorMessage = 'Anda harus login terlebih dahulu.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Anda tidak memiliki akses untuk menghapus data ini.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        }
    });
}

// =========================================
// Dark Mode Toggle Functionality
// =========================================
$(document).ready(function() {
    const $darkModeSwitch = $('#darkModeSwitch');
    const $darkModeIcon = $('#darkModeIcon');
    const $body = $('body');

    // Check if elements exist
    if ($darkModeSwitch.length === 0) {
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
