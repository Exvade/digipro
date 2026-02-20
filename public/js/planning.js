/**
 * Planning JavaScript
 */

$(document).ready(function () {
    // Set success message from Laravel session
    window.successMessage = window.successMessage || null;
    
    // =========================================
    // Index Page - DataTable Initialization
    // =========================================
    // Inisialisasi DataTable untuk planning table
    var planningTable;
    if ($('#planningTable').length > 0) {
        // Destroy existing DataTable instance if it exists
        if ($.fn.dataTable.isDataTable('#planningTable')) {
            $('#planningTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        planningTable = $('#planningTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "destroy": true,
            "order": [],
            "language": {
                "emptyTable": "Belum ada data planning produksi",
                "zeroRecords": "Tidak ada data yang sesuai dengan filter"
            }
        });
    }

    // =========================================
    // Tab Filter Bagian (All, Inject, Assy) - Index Page
    // =========================================
    $('#bagianTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        const bagian = $(this).data('bagian');
        
        if (planningTable) {
            if (bagian === 'all') {
                // Show all rows
                planningTable.search('').columns().search('').draw();
            } else if (bagian === 'inject') {
                // Filter untuk inject (badge-primary)
                planningTable.column(3).search('Inject', true, false).draw();
            } else if (bagian === 'assy') {
                // Filter untuk assy (badge-info / Assembly)
                planningTable.column(3).search('Assembly', true, false).draw();
            }
        }
        
        // Update active tab
        $('#bagianTabs .nav-link').removeClass('active');
        $(this).addClass('active');
    });
    
    // =========================================
    // Index Page - Modal Cetak Functions & Dropdown Filter
    // =========================================
    // Toggle custom date input
    $('#tanggalFilter').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateGroup').show();
        } else {
            $('#customDateGroup').hide();
        }
    });

    // Index dropdown filter logic
    const indexUrl = window.planningIndexUrl;
    const todayYmd = window.todayYmd;
    const tomorrowYmd = window.tomorrowYmd;
    const currentFilter = window.currentFilter;
    const $select = $('#filterTanggalIndex');
    const $customDate = $('#customDateIndex');

    // Only proceed if elements exist
    if ($select.length > 0) {
        // Preselect current
        if (currentFilter === todayYmd) {
            $select.val('today');
        } else if (currentFilter === tomorrowYmd) {
            $select.val('tomorrow');
        } else if (currentFilter) {
            $select.val('custom');
            $customDate.val(currentFilter).show();
        } else {
            $select.val('today');
        }

        $select.on('change', function() {
            const val = $(this).val();
            if (val === 'custom') {
                $customDate.show().focus();
            } else {
                const ymd = val === 'today' ? todayYmd : tomorrowYmd;
                if (indexUrl && ymd) {
                    window.location.href = indexUrl + '?tanggal=' + encodeURIComponent(ymd);
                }
            }
        });

        $customDate.on('change', function() {
            const ymd = $(this).val();
            if (ymd && indexUrl) {
                window.location.href = indexUrl + '?tanggal=' + encodeURIComponent(ymd);
            }
        });
    }

    // =========================================
    // Detail Modal Handler
    // =========================================
    $(document).on('click', '.showBtn', function () {
        const btn = $(this);
        const bagian = btn.data('bagian') || 'inject';

        // Show/hide fields based on bagian
        if (bagian === 'assy') {
            $('#show-bagian-badge').text('Assembly').removeClass('badge-primary').addClass('badge-info');
            $('#row-no_mold').hide();
            $('#row-mesin').hide();
            $('#row-meja').show();
            $('#show-meja').text(btn.data('meja') || '-');
        } else {
            $('#show-bagian-badge').text('Inject').removeClass('badge-info').addClass('badge-primary');
            $('#row-no_mold').show();
            $('#row-mesin').show();
            $('#row-meja').hide();
            $('#show-no_mold').text(btn.data('no_mold') || '-');
            $('#show-mesin').text(btn.data('mesin') || '-');
        }

        // Isi data umum
        $('#show-tanggal').text(btn.data('tanggal') || '-');
        $('#show-lot').text(btn.data('lot') || '-');
        $('#show-no_part').text(btn.data('no_part') || '-');
        $('#show-no_barang').text(btn.data('no_barang') || '-');
        $('#show-nama_part').text(btn.data('nama_part') || '-');
        $('#show-type').text(btn.data('type') || '-');
        $('#show-qty').text(btn.data('qty') || 0);
        
        // Status Approval dengan badge
        const statusApproval = btn.data('status_approval') || 'pending';
        let badgeClass = 'badge-warning';
        let badgeText = 'Pending';
        
        if (statusApproval === 'approved') {
            badgeClass = 'badge-success';
            badgeText = 'Approved';
        } else if (statusApproval === 'rejected') {
            badgeClass = 'badge-danger';
            badgeText = 'Rejected';
        }
        
        $('#show-status-approval').html('<span class="badge ' + badgeClass + '">' + badgeText + '</span>');
        $('#show-catatan').text(btn.data('catatan') || '-');
        
        // Isi data target shift
        $('#show-target_shift_pagi').text(btn.data('target_shift_pagi') || 0);
        $('#show-target_shift_siang').text(btn.data('target_shift_siang') || 0);
        $('#show-target_shift_malam').text(btn.data('target_shift_malam') || 0);
        $('#show-total_shift').text(btn.data('total_shift') || 0);

        // Reset semua cell ke 0
        $('[id^="pagi_jam_"], [id^="siang_jam_"], [id^="malam_jam_"]').text(0);

        // Ambil data jam dari JSON
        const jamData = btn.data('jam') || [];
        
        // Buat mapping untuk memudahkan pencarian
        const jamMap = {};
        jamData.forEach(function(item) {
            // Robust check for shift property (handle potential typo or undefined)
            let shiftName = '';
            if (item.shift) {
                shiftName = item.shift.toLowerCase();
            } else if (item[' shift']) {
                shiftName = item[' shift'].toLowerCase();
            }
            
            if (shiftName && item.jam) {
                const hour = parseInt(item.jam.split(':')[0]);
                jamMap[shiftName + '_' + hour] = item.target;
            }
        });

        // SHIFT 1 (PAGI) - 07:00 sampai 14:00
        let totalPagi = 0;
        for (let i = 7; i <= 14; i++) {
            const nilai = parseInt(jamMap['pagi_' + i] || 0);
            $('#pagi_jam_' + String(i).padStart(2, '0')).text(nilai);
            totalPagi += nilai;
        }
        $('#total-pagi').text(totalPagi);

        // SHIFT 2 (SIANG) - 15:00 sampai 22:00
        let totalSiang = 0;
        for (let i = 15; i <= 22; i++) {
            const nilai = parseInt(jamMap['siang_' + i] || 0);
            $('#siang_jam_' + i).text(nilai);
            totalSiang += nilai;
        }
        $('#total-siang').text(totalSiang);

        // SHIFT 3 (MALAM) - 23:00, 00:00 sampai 06:00
        let totalMalam = 0;
        const jamMalam = [23, 0, 1, 2, 3, 4, 5, 6];
        jamMalam.forEach(function(i) {
            const nilai = parseInt(jamMap['malam_' + i] || 0);
            $('#malam_jam_' + String(i).padStart(2, '0')).text(nilai);
            totalMalam += nilai;
        });
        $('#total-malam').text(totalMalam);

        // Grand Total
        const grandTotal = totalPagi + totalSiang + totalMalam;
        $('#show-grandtotal').text(grandTotal);

        // Tampilkan modal
        $('#modalShow').modal('show');
    });

    // =========================================
    // Edit Modal Handler
    // =========================================
    $(document).on('click', '.editBtn', function () {
        const btn = $(this);
        const id = btn.data('id');
        const bagian = btn.data('bagian') || 'inject';
    
        // Reset form
        $('#updateForm')[0].reset();
    
        // Set bagian dulu
        $('#update_bagian').val(bagian);
        
        // Toggle visibility berdasarkan bagian
        if (bagian === 'inject') {
            $('#update-mold-mesin-card').show();
            $('#update-meja-card').hide();
            $('#update_moldshop_id').prop('required', true).removeAttr('disabled').attr('name', 'moldshop_id');
            $('#update_mesin_id').prop('required', true).removeAttr('disabled').attr('name', 'mesin_id');
            $('#update_meja_id').prop('required', false).val('').attr('disabled', 'disabled').removeAttr('name');
        } else if (bagian === 'assy') {
            $('#update-mold-mesin-card').hide();
            $('#update-meja-card').show();
            $('#update_moldshop_id').prop('required', false).val('').attr('disabled', 'disabled').removeAttr('name');
            $('#update_mesin_id').prop('required', false).val('').attr('disabled', 'disabled').removeAttr('name');
            $('#update_meja_id').prop('required', true).removeAttr('disabled').attr('name', 'meja_id');
        }
        
        // Tunggu sebentar agar toggle selesai, baru isi nilai field
        setTimeout(function() {
            if (bagian === 'inject') {
                $('#update_moldshop_id').val(btn.data('moldshop_id') || '').trigger('change');
                $('#update_mesin_id').val(btn.data('mesin_id') || '').trigger('change');
            } else if (bagian === 'assy') {
                $('#update_meja_id').val(btn.data('meja_id') || '').trigger('change');
            }
            
            $('#update_part_id').val(btn.data('part_id')).trigger('change');
            $('#update_tanggal').val(btn.data('tanggal'));
            $('#update_lot_produksi').val(btn.data('lot_produksi'));
            $('#update_qty_planning_produksi').val(btn.data('qty'));
            $('#update_catatan').val(btn.data('catatan'));
        }, 200);
    
        // Target shift
        $('#update_target_shift_pagi').val(btn.data('target_shift_pagi'));
        $('#update_target_shift_siang').val(btn.data('target_shift_siang'));
        $('#update_target_shift_malam').val(btn.data('target_shift_malam'));
        $('#update_total_shift').val(btn.data('total_shift'));
    
        // Ambil data jam dari JSON
        const jamData = btn.data('jam') || [];
        
        // Buat mapping untuk memudahkan pencarian
        const jamMap = {};
        jamData.forEach(function(item) {
            const hour = parseInt(item.jam.split(':')[0]);
            jamMap[item.shift + '_' + hour] = item.target;
        });
    
        // Shift 1 (Pagi)
        for (let i = 7; i <= 14; i++) {
            const nilai = parseInt(jamMap['pagi_' + i] || 0);
            $('#update_pagi_jam_' + String(i).padStart(2,'0')).val(nilai);
        }
    
        // Shift 2 (Siang)
        for (let i = 15; i <= 22; i++) {
            const nilai = parseInt(jamMap['siang_' + i] || 0);
            $('#update_siang_jam_' + String(i).padStart(2,'0')).val(nilai);
        }
    
        // Shift 3 (Malam)
        [23,0,1,2,3,4,5,6].forEach(i => {
            const nilai = parseInt(jamMap['malam_' + i] || 0);
            $('#update_malam_jam_' + String(i).padStart(2,'0')).val(nilai);
        });
    
        // Set action form sesuai route resource
        $('#updateForm').attr('action', '/planning/' + id);
    
        // Show modal
        $('#modalUpdate').modal('show');
    });
    

    // =========================================
    // Delete Handler + SweetAlert
    // =========================================
    $(document).on('click', '.deleteForm button[type="submit"]', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Yakin hapus?',
            text: 'Data yang dihapus tidak bisa dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // =========================================
    // Pesan sukses flash dari Laravel
    // =========================================
    // Approval Button Handler
    // =========================================
    $(document).on('click', '.approvalBtn', function() {
        const planningId = $(this).data('id');
        const action = $(this).data('action'); // 'approve' or 'reject'
        
        // Set form values
        $('#approval_planning_id').val(planningId);
        $('#approval_action').val(action);
        
        // Show/hide content based on action
        if (action === 'approve') {
            $('#approveContent').show();
            $('#rejectContent').hide();
            $('#approvalModalHeader').removeClass('bg-danger').addClass('bg-success');
            $('#modalApprovalLabel').html('<i class="fas fa-check-circle"></i> Approve Planning');
            $('#submitApprovalBtn').removeClass('btn-danger').addClass('btn-success');
            $('#submitApprovalBtn').html('<i class="fas fa-check"></i> Setujui');
        } else {
            $('#approveContent').hide();
            $('#rejectContent').show();
            $('#approvalModalHeader').removeClass('bg-success').addClass('bg-danger');
            $('#modalApprovalLabel').html('<i class="fas fa-times-circle"></i> Reject Planning');
            $('#submitApprovalBtn').removeClass('btn-success').addClass('btn-danger');
            $('#submitApprovalBtn').html('<i class="fas fa-times"></i> Tolak');
            $('#rejection_reason').val(''); // Clear previous value
        }
        
        // Show modal
        $('#modalApproval').modal('show');
    });

    // =========================================
    if (typeof window.successMessage !== 'undefined' && window.successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: window.successMessage,
            showConfirmButton: false,
            timer: 2000
        });
    }
});

/**
 * Submit approval form
 */
function submitApproval() {
    const form = $('#formApproval');
    const action = $('#approval_action').val();
    
    // Validate rejection reason if rejecting
    if (action === 'reject') {
        const rejectionReason = $('#rejection_reason').val().trim();
        if (!rejectionReason) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Alasan penolakan wajib diisi!'
            });
            return;
        }
    }
    
    // Disable submit button
    $('#submitApprovalBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    
    $.ajax({
        url: '/planning/approval',
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
                    $('#modalApproval').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Terjadi kesalahan saat memproses approval.'
                });
                $('#submitApprovalBtn').prop('disabled', false);
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
            $('#submitApprovalBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Konfirmasi');
        }
    });
}

/**
 * Submit cetak form (Index Page)
 */
function submitCetak() {
    const tanggalFilter = document.getElementById('tanggalFilter').value;
    const customDate = document.getElementById('customDate').value;
    const bagianFilter = document.getElementById('bagianFilter').value;
    const baseUrl = window.planningCetakUrl || '/planning/cetak';
    
    let tanggalValue = '';
    
    // Set parameter tanggal
    if (tanggalFilter === 'custom') {
        if (!customDate) {
            alert('Silakan pilih tanggal spesifik!');
            return;
        }
        tanggalValue = customDate;
    } else {
        tanggalValue = tanggalFilter;
    }
    
    // Buat URL dengan parameter yang benar
    const url = baseUrl + "?tanggal=" + encodeURIComponent(tanggalValue) + "&bagian=" + encodeURIComponent(bagianFilter);
    
    // Buka di tab baru
    window.open(url, '_blank');
    
    // Tutup modal
    $('#modalCetak').modal('hide');
}
