/**
 * Admin Documents Page JavaScript
 * Handles file input labels and form validation
 */

$(document).ready(function() {
    // Update custom file input label when file is selected
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'Pilih file...');
    });

    // Disable form submission for maintenance/inactive machines
    $('form.upload-form').on('submit', function(e) {
        var $form = $(this);
        var isDisabled = $form.closest('tr').hasClass('machine-disabled');
        
        if (isDisabled) {
            e.preventDefault();
            // Check if SweetAlert is available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Upload Dibatalkan',
                    text: 'Mesin dengan status Maintenance atau Tidak Aktif tidak dapat mengupload dokumen.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Mesin dengan status Maintenance atau Tidak Aktif tidak dapat mengupload dokumen.');
            }
            return false;
        }
    });

    // Show tooltip for disabled status
    $('.machine-disabled').each(function() {
        var status = $(this).find('.status-badge').text().trim().toLowerCase();
        if (status === 'maintenance' || status === 'tidak aktif' || status === 'offline') {
            $(this).attr('title', 'Mesin ini tidak dapat mengupload dokumen karena status ' + status);
        }
    });

    // Konfigurasi DataTable
    var dataTableConfig = {
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "paging": true, // Aktifkan pagination
        "pageLength": 10, // Tampilkan 10 data per halaman
        "lengthChange": false, // Sembunyikan dropdown "Show entries"
        "info": true, // Tampilkan info counter
        "ordering": false, // Nonaktifkan semua sorting
        "searching": true, // Aktifkan search box
        "responsive": true,
        "autoWidth": false,
        "dom": '<"row"<"col-sm-12"f>>' + // Search box di atas
               '<"row"<"col-sm-12"tr>>' + // Table
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // Info + Pagination di bawah
        "drawCallback": function() {
            // Re-initialize custom file input after table redraw
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });
        }
    };

    // Initialize DataTable untuk Mesin
    var mesinTable = $('#mesin-table').DataTable(dataTableConfig);

    // Initialize DataTable untuk Meja
    var mejaTable = $('#meja-table').DataTable(dataTableConfig);

    // Handle tab switch to redraw tables
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });

    // Handle document view button click
    $(document).on('click', '.btn-view-document', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var docUrl = $(this).data('doc-url');
        var docName = $(this).data('doc-name') || 'Dokumen';
        
        if (!docUrl) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'URL dokumen tidak ditemukan.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('URL dokumen tidak ditemukan.');
            }
            return;
        }
        
        // Set modal content
        $('#documentViewerModal .modal-title').html('<i class="fas fa-file-pdf mr-2"></i>' + docName);
        $('#documentViewerModal iframe').attr('src', docUrl);
        $('#documentViewerModal .btn-download-doc').attr('href', docUrl).attr('download', docName);
        
        // Show modal
        $('#documentViewerModal').modal('show');
    });

    // Clear iframe src when modal is hidden to prevent memory leaks
    $('#documentViewerModal').on('hidden.bs.modal', function() {
        $('#documentViewerModal iframe').attr('src', '');
    });
});
