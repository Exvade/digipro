$(document).ready(function() {
    // Initialize DataTable
    var approvalTable;
    if ($('#approvalTable').length > 0) {
        approvalTable = $('#approvalTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false
        });
    }

    // =========================================
    // Tab Filter Bagian (All, Inject, Assy) - Approval Page
    // =========================================
    $('#bagianTabsApproval .nav-link').on('click', function(e) {
        e.preventDefault();
        const bagian = $(this).data('bagian');
        
        if (approvalTable) {
            if (bagian === 'all') {
                // Show all rows
                approvalTable.search('').columns().search('').draw();
            } else if (bagian === 'inject') {
                // Filter untuk inject (badge-primary)
                approvalTable.column(3).search('Inject', true, false).draw();
            } else if (bagian === 'assy') {
                // Filter untuk assy (badge-info / Assembly)
                approvalTable.column(3).search('Assembly', true, false).draw();
            }
        }
        
        // Update active tab
        $('#bagianTabsApproval .nav-link').removeClass('active');
        $(this).addClass('active');
    });

    // Approve Button Handler
    $(document).on('click', '.approveBtn', function() {
        const planningId = $(this).data('id');
        const noPart = $(this).data('no_part');
        const namaPart = $(this).data('nama_part');
        const qty = $(this).data('qty');
        
        $('#approval_planning_id').val(planningId);
        $('#approval-no_part').text(noPart);
        $('#approval-nama_part').text(namaPart);
        $('#approval-qty').text(qty);
        $('#qrCodeDisplay').hide();
        $('#approveContent').show();
        $('#submitApprovalBtn').show().prop('disabled', false).html('<i class="fas fa-check"></i> Konfirmasi Approve');
        $('#modalApproval').modal('show');
    });

    // Reject Button Handler
    $(document).on('click', '.rejectBtn', function() {
        const planningId = $(this).data('id');
        $('#reject_planning_id').val(planningId);
        $('#rejection_reason').val('');
        $('#modalReject').modal('show');
    });

    // View Button Handler - Load data dari server
    $(document).on('click', '.viewBtn', function() {
        const planningId = $(this).data('id');
        
        // Load planning data via AJAX
        $.ajax({
            url: '/planning/' + planningId,
            method: 'GET',
            success: function(response) {
                console.log('Planning Data:', response);
                
                // 1. Populate Informasi Umum
                $('#show-bagian-badge').text(response.bagian === 'assy' ? 'Assembly' : 'Inject')
                    .removeClass('badge-primary badge-info')
                    .addClass(response.bagian === 'assy' ? 'badge-info' : 'badge-primary');
                    
                // Handle visibility based on bagian
                if (response.bagian === 'inject') {
                    $('#row-no_mold').show();
                    $('#row-mesin').show();
                    $('#row-meja').hide();
                    $('#show-no_mold').text(response.no_mold || '-');
                    $('#show-mesin').text(response.mesin || '-');
                } else {
                    $('#row-no_mold').hide();
                    $('#row-mesin').hide();
                    $('#row-meja').show();
                    $('#show-meja').text(response.meja || '-'); // Pastikan controller return meja
                }
                
                $('#show-tanggal').text(response.tanggal || '-');
                $('#show-lot').text(response.lot || '-');
                $('#show-no_part').text(response.no_part || '-');
                $('#show-nama_part').text(response.nama_part || '-');
                $('#show-type').text(response.type || '-');
                $('#show-qty').text(response.qty ? new Intl.NumberFormat('id-ID').format(response.qty) : '-');
                $('#show-status-approval').text(response.status_approval || response.status || '-'); // Handle naming difference
                $('#show-catatan').text(response.catatan || '-');
                
                // 2. Populate Target Shift
                $('#show-target_shift_pagi').text(response.target_shift_pagi || 0);
                $('#show-target_shift_siang').text(response.target_shift_siang || 0);
                $('#show-target_shift_malam').text(response.target_shift_malam || 0);
                $('#show-total_shift').text(response.total_shift || 0);
                
                // 3. Populate Hourly Data & Calculate Totals
                let totalPagi = 0;
                let totalSiang = 0;
                let totalMalam = 0;
                
                // Shift Pagi (07-14)
                for (let i = 7; i <= 14; i++) {
                    let key = 'pagi_jam_' + String(i).padStart(2, '0');
                    let val = parseInt(response[key]) || 0;
                    $('#pagi_jam_' + String(i).padStart(2, '0')).text(val);
                    totalPagi += val;
                }
                $('#total-pagi').text(totalPagi);
                
                // Shift Siang (15-22)
                for (let i = 15; i <= 22; i++) {
                    let key = 'siang_jam_' + String(i).padStart(2, '0');
                    let val = parseInt(response[key]) || 0;
                    $('#siang_jam_' + i).text(val); 
                    totalSiang += val;
                }
                $('#total-siang').text(totalSiang);
                
                // Shift Malam (23, 00-06)
                [23, 0, 1, 2, 3, 4, 5, 6].forEach(function(i) {
                    let key = 'malam_jam_' + String(i).padStart(2, '0');
                    let val = parseInt(response[key]) || 0;
                    $('#malam_jam_' + String(i).padStart(2, '0')).text(val);
                    totalMalam += val;
                });
                $('#total-malam').text(totalMalam);
                
                // Grand Total
                $('#show-grandtotal').text(new Intl.NumberFormat('id-ID').format(totalPagi + totalSiang + totalMalam));
                
                // Show modal
                $('#modalShow').modal('show');
            },
            error: function(xhr) {
                console.error('Error loading planning:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data planning'
                });
            }
        });
    });

    // Attach click handler for submit approval
    $('#submitApprovalBtn').on('click', function() {
        submitApproval();
    });

    // Attach click handler for submit reject
    $('#btnConfirmReject').on('click', function() {
        submitReject();
    });
});

// Submit Approval
function submitApproval() {
    const form = $('#formApproval');
    const url = form.data('action-url');
    
    $('#submitApprovalBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    
    $.ajax({
        url: url,
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Tutup modal
                $('#modalApproval').modal('hide');
                
                // Tampilkan sweet alert terima kasih
                Swal.fire({
                    icon: 'success',
                    title: 'Terima Kasih',
                    text: 'Planning berhasil disetujui. QR code akan muncul saat cetak.',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memproses approval.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
            $('#submitApprovalBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Konfirmasi Approve');
        }
    });
}

// Submit Reject
function submitReject() {
    const form = $('#formReject');
    const url = form.data('action-url');
    const rejectionReason = $('#rejection_reason').val().trim();
    
    if (!rejectionReason) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Alasan penolakan wajib diisi!'
        });
        return;
    }
    
    $.ajax({
        url: url,
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    $('#modalReject').modal('hide');
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memproses rejection.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
}
