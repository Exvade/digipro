/* Checksheet Index Page JavaScript */

$(document).ready(function() {
    // Initialize DataTables for both tables
    var injectTable = $('#injectChecksheetTable');
    var assyTable = $('#assyChecksheetTable');
    
    // Initialize DataTables with proper empty state handling
    var injectDataTable = injectTable.DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
            "emptyTable": ""
        },
        "pageLength": 25,
        "order": [[1, "desc"]],
        "drawCallback": function(settings) {
            // Show empty state if no data
            if (this.api().data().length === 0) {
                var emptyRow = '<tr><td colspan="9" class="text-center text-muted py-4">' +
                    '<i class="fas fa-clipboard-check fa-3x mb-3 d-block"></i>' +
                    '<h5>Tidak ada data checksheet inject</h5></td></tr>';
                injectTable.find('tbody').html(emptyRow);
            }
        }
    });

    var assyDataTable = assyTable.DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
            "emptyTable": ""
        },
        "pageLength": 25,
        "order": [[1, "desc"]],
        "drawCallback": function(settings) {
            // Show empty state if no data
            if (this.api().data().length === 0) {
                var emptyRow = '<tr><td colspan="9" class="text-center text-muted py-4">' +
                    '<i class="fas fa-clipboard-check fa-3x mb-3 d-block"></i>' +
                    '<h5>Tidak ada data checksheet assy</h5></td></tr>';
                assyTable.find('tbody').html(emptyRow);
            }
        }
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
            injectDataTable.columns.adjust().draw();
        } else if (e.target.id === 'assy-tab') {
            assyDataTable.columns.adjust().draw();
        }
    });

    // Auto-submit filter form when select changes
    $('#grup_id, #bagian').change(function() {
        $('#filterForm').submit();
    });

    // ✅ Auto-submit filter form when single date changes
    $('#tanggal').change(function() {
        $('#filterForm').submit();
    });
});

    // Event Listeners for Action Buttons
    $(document).on('click', '.btn-view-checksheet', function() {
        var id = $(this).data('id');
        $.get(`/checksheet/${id}`, function(response) {
            $('#viewChecksheetContent').html(response);
            $('#viewChecksheetModal').modal('show');
        });
    });

    $(document).on('click', '.btn-edit-checksheet', function() {
        var id = $(this).data('id');
        $.get(`/checksheet/${id}/edit`, function(response) {
            $('#editChecksheetContent').html(response);
            $('#editChecksheetModal').modal('show');
        });
    });

    $(document).on('click', '.btn-delete-checksheet', function() {
        var id = $(this).data('id');
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
    });
