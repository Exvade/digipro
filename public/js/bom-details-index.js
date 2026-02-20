/**
 * BOM Details Index - JavaScript
 */

$(document).ready(function() {
    // Inisialisasi DataTable
    $('#tableBomDetails').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        responsive: false,
        autoWidth: false,
        columnDefs: [
            {
                orderable: false,
                targets: 8 // Kolom aksi tidak bisa di-sort
            },
            {
                className: 'text-center',
                targets: [0, 3, 4, 6, 8] // Kolom center
            }
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Modal create - Mold selection handler
    $('#moldSelect').on('change', function() {
        let selectedOption = $(this).find(':selected');
        let namaMold = selectedOption.data('nama');
        
        if (namaMold && selectedOption.val() !== '') {
            $('#moldNameText').text(namaMold);
            $('#moldNameDisplay').removeClass('d-none').addClass('d-block');
            
            // Tambahkan efek visual
            $('#moldNameDisplay').addClass('text-success');
            setTimeout(function() {
                $('#moldNameDisplay').removeClass('text-success').addClass('text-muted');
            }, 2000);
        } else {
            $('#moldNameDisplay').addClass('d-none').removeClass('d-block');
        }
    });
    
    // Trigger change event jika ada nilai yang sudah dipilih
    $('#moldSelect').trigger('change');

    // Modal edit - Handle edit button clicks
    $(document).on('click', '.editBtn', function() {
        const id = $(this).data('id');
        const $form = $('#editForm');
        
        $form.attr('action', `/bom-details/${id}`);

        $('#edit_moldshop_id').val($(this).data('moldshop_id'));
        $('#edit_part_id').val($(this).data('part_id'));
        $('#edit_cavity_mold').val($(this).data('cavity_mold'));
        $('#edit_cycle_time_std').val($(this).data('cycle_time_std'));
        $('#edit_resin_type').val($(this).data('resin_type'));
        $('#edit_shot_weight').val($(this).data('shot_weight'));
        $('#edit_remarks').val($(this).data('remarks'));

        // Show modal (Bootstrap 4)
        $('#modalEdit').modal('show');
    });

    // Handle Delete Confirmation
    $(document).on('click', '.deleteBtn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: $('body').hasClass('dark-mode') ? '#2d2d2d' : '#fff',
            color: $('body').hasClass('dark-mode') ? '#e0e0e0' : '#545454'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
