/* Checksheet Index Page JavaScript */

$(document).ready(function() {
    // Initialize DataTables for both tables
    $('#injectChecksheetTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });

    $('#assyChecksheetTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });

    // Load create form when modal is shown
    $('#createChecksheetModal').on('show.bs.modal', function() {
        $.get('/checksheet/create', function(response) {
            $('#createChecksheetContent').html(response);
        });
    });

    // Reinitialize DataTables when switching tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'inject-tab') {
            $('#injectChecksheetTable').DataTable().columns.adjust();
        } else if (e.target.id === 'assy-tab') {
            $('#assyChecksheetTable').DataTable().columns.adjust();
        }
    });

    // Auto-submit filter form when select changes
    $('#grup_id, #bagian').change(function() {
        $('#filterForm').submit();
    });
});

function viewChecksheet(id) {
    $.get(`/checksheet/${id}`, function(response) {
        $('#viewChecksheetContent').html(response);
        $('#viewChecksheetModal').modal('show');
    });
}

function editChecksheet(id) {
    $.get(`/checksheet/${id}/edit`, function(response) {
        $('#editChecksheetContent').html(response);
        $('#editChecksheetModal').modal('show');
    });
}

function deleteChecksheet(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data checksheet akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/checksheet/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus checksheet'
                    });
                }
            });
        }
    });
} 