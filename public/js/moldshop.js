$(document).ready(function() {

    // =========================
    // 0. Success Message Handler
    // =========================
    const successMessageDiv = $('#success-message');
    const successMessage = successMessageDiv.data('message');
    
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: successMessage,
            showConfirmButton: false,
            timer: 2000
        });
    }

    // =========================
    // 1. Inisialisasi DataTables
    // =========================
    if ($('#moldTable').length > 0 && !$.fn.DataTable.isDataTable('#moldTable')) {
        $('#moldTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Semua"]
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            columnDefs: [{
                orderable: false,
                targets: [0, -1] // Disable sort on first and last columns
            }]
        });
    }

    if ($('#historyTable').length > 0 && !$.fn.DataTable.isDataTable('#historyTable')) {
        $('#historyTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[6, 'desc']], // Order by total shoot by default
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
    }

    // =========================
    // 2. Modal Create
    // =========================
    // Reset form ketika modal Create ditutup
    $('#modalCreate').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // Ensure proper modal behavior
    $('#modalCreate').modal({
        backdrop: true,
        keyboard: true,
        show: false
    });

    // =========================
    // 3. Modal Edit (Update)
    // =========================
    $(document).on('click', '.editBtn', function() {
        const id = $(this).data('id');
        if (!id) {
            console.error('Edit button clicked but ID not found');
            return;
        }

        // Cek modal dan field
        const modal = $('#modalUpdate');
        if (modal.length === 0) {
            console.error('#modalUpdate not found in DOM');
            return;
        }

        const fields = ['update_no_mold', 'update_nama_mold', 'update_type', 'update_customer', 'update_status'];
        let missing = fields.filter(f => $('#' + f).length === 0);
        if (missing.length > 0) {
            console.error('Missing fields in update modal:', missing);
            return;
        }

        // Set value form
        $('#update_no_mold').val($(this).data('no_mold') || '');
        $('#update_nama_mold').val($(this).data('nama_mold') || '');
        $('#update_type').val($(this).data('type') || '');
        $('#update_customer').val($(this).data('customer') || '');
        $('#update_status').val($(this).data('status') || '');
        $('#updateForm').attr('action', '/moldshop/' + id);

        $('#modalUpdate').modal('show');
    });

    // Reset form ketika modal Update ditutup
    $('#modalUpdate').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // Ensure proper modal behavior for update modal
    $('#modalUpdate').modal({
        backdrop: true,
        keyboard: true,
        show: false
    });

    // Handle backdrop click to close modal
    $(document).on('click', '.modal-backdrop', function() {
        $('.modal').modal('hide');
    });

    // =========================
    // 4. Validasi Form Sebelum Submit
    // =========================
    $('#updateForm').on('submit', function(e) {
        const requiredFields = ['update_no_mold','update_nama_mold','update_type','update_customer','update_status'];
        let isValid = true;
        let emptyFields = [];

        requiredFields.forEach(function(name) {
            const field = $('#' + name);
            if (!field.val().trim()) {
                isValid = false;
                emptyFields.push(name);
                field.addClass('is-invalid');
            } else {
                field.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Mohon lengkapi semua field: ' + emptyFields.join(', ')
            });
        }
    });

    $('#modalCreate form').on('submit', function() {
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // =========================
    // 5. Delete dengan SweetAlert
    // =========================
    $(document).on('submit', '.deleteForm', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    });

    // =========================
    // 6. View Usage History (AJAX)
    // =========================
    $(document).on('click', '.viewUsageBtn', function() {
        const id = $(this).data('id');
        const noMold = $(this).data('no');
        
        $('#detailNoMold').text(noMold);
        $('#usageTableBody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>');
        $('#modalUsage').modal('show');

        $.ajax({
            url: `/moldshop/${id}/usage`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.usage.length > 0) {
                        response.usage.forEach(item => {
                            html += `<tr class="text-center">
                                <td>${item.tanggal}</td>
                                <td class="text-left">${item.part}</td>
                                <td>${item.ok.toLocaleString()}</td>
                                <td>${item.ng.toLocaleString()}</td>
                                <td class="font-weight-bold">${item.total.toLocaleString()}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat penggunaan untuk mold ini.</td></tr>';
                    }
                    $('#usageTableBody').html(html);
                }
            },
            error: function() {
                $('#usageTableBody').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    });

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
