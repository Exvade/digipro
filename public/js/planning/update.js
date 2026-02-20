(function($) {
    'use strict';
    
    console.log('Planning Update Script Loaded');
    
    // Fungsi untuk toggle visibility berdasarkan bagian
    function toggleBagianFields(bagian) {
        console.log('Toggle bagian:', bagian);
        
        if (bagian === 'inject') {
            // Tampilkan Mold & Mesin, sembunyikan Meja
            $('#update-mold-mesin-card').show();
            $('#update-meja-card').hide();
            $('#update_moldshop_id').prop('required', true).prop('disabled', false);
            $('#update_mesin_id').prop('required', true).prop('disabled', false);
            $('#update_meja_id').prop('required', false).val('').prop('disabled', true);
            console.log('Inject selected - Mold & Mesin shown');
        } else if (bagian === 'assy') {
            // Sembunyikan Mold & Mesin, tampilkan Meja
            $('#update-mold-mesin-card').hide();
            $('#update-meja-card').show();
            $('#update_moldshop_id').prop('required', false).val('').prop('disabled', true);
            $('#update_mesin_id').prop('required', false).val('').prop('disabled', true);
            $('#update_meja_id').prop('required', true).prop('disabled', false);
            console.log('Assembly selected - Meja shown');
        } else {
            // Reset semua - sembunyikan semua card
            $('#update-mold-mesin-card').hide();
            $('#update-meja-card').hide();
            $('#update_moldshop_id').prop('required', false).val('').prop('disabled', true);
            $('#update_mesin_id').prop('required', false).val('').prop('disabled', true);
            $('#update_meja_id').prop('required', false).val('').prop('disabled', true);
            console.log('No bagian selected - All cards hidden');
        }
    }
    
    // Event handler untuk perubahan bagian - gunakan event delegation
    $(document).on('change', '#update_bagian', function() {
        const bagian = $(this).val();
        console.log('Bagian changed to:', bagian);
        toggleBagianFields(bagian);
    });
    
    // Handle form submit - pastikan field yang tidak digunakan dikirim sebagai null
    $('.formUpdatePlanning').on('submit', function(e) {
        const bagian = $('#update_bagian').val();
        
        // Validasi total shift vs qty planning
        const qtyPlanning = parseInt($('#update_qty_planning_produksi').val()) || 0;
        const totalShift = parseInt($('#update_total_shift').val()) || 0;
        
        if (qtyPlanning > 0 && totalShift < qtyPlanning) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Tidak Dapat Menyimpan!',
                html: `<p>Total Target Shift (<strong>${totalShift}</strong>) kurang dari Qty Planning (<strong>${qtyPlanning}</strong>).</p>
                       <p>Kekurangan: <strong>${qtyPlanning - totalShift}</strong></p>
                       <p class="text-muted mb-0">Silakan sesuaikan target shift agar minimal sama dengan qty planning.</p>`,
                confirmButtonText: 'OK, Saya Mengerti'
            });
            return false;
        }

        // Validasi Total Hourly vs Target Shift
        const targetPagi = parseInt($('#update_target_shift_pagi').val()) || 0;
        const totalPagi = parseInt($('#update_total_pagi').text()) || 0;
        if (totalPagi > targetPagi) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: `Total jam Shift Pagi (${totalPagi}) melebihi Target Shift Pagi (${targetPagi}).`,
            });
            return false;
        }

        const targetSiang = parseInt($('#update_target_shift_siang').val()) || 0;
        const totalSiang = parseInt($('#update_total_siang').text()) || 0;
        if (totalSiang > targetSiang) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: `Total jam Shift Siang (${totalSiang}) melebihi Target Shift Siang (${targetSiang}).`,
            });
            return false;
        }

        const targetMalam = parseInt($('#update_target_shift_malam').val()) || 0;
        const totalMalam = parseInt($('#update_total_malam').text()) || 0;
        if (totalMalam > targetMalam) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: `Total jam Shift Malam (${totalMalam}) melebihi Target Shift Malam (${targetMalam}).`,
            });
            return false;
        }
        
        // Enable semua field dulu agar bisa dikirim
        $('#update_moldshop_id').prop('disabled', false);
        $('#update_mesin_id').prop('disabled', false);
        $('#update_meja_id').prop('disabled', false);
        
        if (bagian === 'inject') {
            // Inject: mold & mesin terisi, meja kosong
            if (!$('#update_meja_id').val()) {
                $('#update_meja_id').val('');
            }
        } else if (bagian === 'assy') {
            // Assembly: meja terisi, mold & mesin kosong
            if (!$('#update_moldshop_id').val()) {
                $('#update_moldshop_id').val('');
            }
            if (!$('#update_mesin_id').val()) {
                $('#update_mesin_id').val('');
            }
        }
        
        console.log('Form submitted with bagian:', bagian);
        console.log('moldshop_id:', $('#update_moldshop_id').val() || 'empty');
        console.log('mesin_id:', $('#update_mesin_id').val() || 'empty');
        console.log('meja_id:', $('#update_meja_id').val() || 'empty');

        // Disable submit button to prevent double submission
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });
    
    // Set initial state saat modal dibuka
    $('#modalUpdate').on('show.bs.modal', function() {
        console.log('Modal Update opened');
        const bagian = $('#update_bagian').val();
        if (bagian) {
            // Trigger toggle berdasarkan bagian yang sudah ada
            toggleBagianFields(bagian);
        } else {
            // Jika tidak ada bagian, sembunyikan semua
            $('#update-mold-mesin-card').hide();
            $('#update-meja-card').hide();
        }
    });
    
    // Juga trigger saat modal sudah fully shown
    $('#modalUpdate').on('shown.bs.modal', function() {
        console.log('Modal Update fully shown');
        const bagian = $('#update_bagian').val();
        if (bagian) {
            toggleBagianFields(bagian);
        }
        // Calculate total shift saat modal dibuka
        calculateUpdateTotalShift();
    });
    
    // Auto-calculate total_shift untuk update form
    function calculateUpdateTotalShift() {
        const pagi = parseInt($('#update_target_shift_pagi').val()) || 0;
        const siang = parseInt($('#update_target_shift_siang').val()) || 0;
        const malam = parseInt($('#update_target_shift_malam').val()) || 0;
        const total = pagi + siang + malam;
        $('#update_total_shift').val(total);
        console.log('Update total shift calculated:', total);
        
        // Validasi dengan qty_planning_produksi
        validateUpdateTotalShift();
    }
    
    // Validasi total shift vs qty planning untuk update form
    function validateUpdateTotalShift() {
        const qtyPlanning = parseInt($('#update_qty_planning_produksi').val()) || 0;
        const totalShift = parseInt($('#update_total_shift').val()) || 0;
        
        // Hapus alert sebelumnya jika ada
        $('.alert-target-shift-update').remove();
        
        if (qtyPlanning > 0 && totalShift > 0 && totalShift < qtyPlanning) {
            const selisih = qtyPlanning - totalShift;
            const alertHtml = `
                <div class="alert alert-warning alert-dismissible fade show alert-target-shift-update mt-2" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Peringatan!</strong> Total Target Shift (${totalShift}) kurang dari Qty Planning (${qtyPlanning}). 
                    Kekurangan: <strong>${selisih}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('#update_total_shift').closest('.form-group').append(alertHtml);
        } else if (qtyPlanning > 0 && totalShift > qtyPlanning) {
            const kelebihan = totalShift - qtyPlanning;
            const alertHtml = `
                <div class="alert alert-info alert-dismissible fade show alert-target-shift-update mt-2" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Info:</strong> Total Target Shift (${totalShift}) melebihi Qty Planning (${qtyPlanning}). 
                    Kelebihan: <strong>${kelebihan}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('#update_total_shift').closest('.form-group').append(alertHtml);
        }
    }
    
    // Trigger calculation saat input berubah
    $('#update_target_shift_pagi, #update_target_shift_siang, #update_target_shift_malam, #update_qty_planning_produksi').on('input change', function() {
        calculateUpdateTotalShift();
        calculateUpdateHourlyTotals();
    });

    // Calculate hourly totals for update
    function calculateUpdateHourlyTotals() {
        // Shift Pagi
        let totalPagi = 0;
        $('#modalUpdate input[name^="pagi_jam_"]').each(function() {
            totalPagi += parseInt($(this).val()) || 0;
        });
        $('#update_total_pagi').text(totalPagi);

        // Shift Siang
        let totalSiang = 0;
        $('#modalUpdate input[name^="siang_jam_"]').each(function() {
            totalSiang += parseInt($(this).val()) || 0;
        });
        $('#update_total_siang').text(totalSiang);

        // Shift Malam
        let totalMalam = 0;
        $('#modalUpdate input[name^="malam_jam_"]').each(function() {
            totalMalam += parseInt($(this).val()) || 0;
        });
        $('#update_total_malam').text(totalMalam);

        // Visual feedback
        validateUpdateShiftTotal('pagi', totalPagi);
        validateUpdateShiftTotal('siang', totalSiang);
        validateUpdateShiftTotal('malam', totalMalam);
    }

    function validateUpdateShiftTotal(shift, total) {
        const target = parseInt($(`#update_target_shift_${shift}`).val()) || 0;
        const totalElem = $(`#update_total_${shift}`);
        
        if (total > target) {
            totalElem.addClass('text-danger').removeClass('text-success');
            totalElem.closest('tr').addClass('table-danger');
        } else if (total === target && target > 0) {
            totalElem.addClass('text-success').removeClass('text-danger');
            totalElem.closest('tr').removeClass('table-danger');
        } else {
            totalElem.removeClass('text-danger text-success');
            totalElem.closest('tr').removeClass('table-danger');
        }
    }

    // Listen for hourly input changes
    $('#modalUpdate input[name^="pagi_jam_"], #modalUpdate input[name^="siang_jam_"], #modalUpdate input[name^="malam_jam_"]').on('input', function() {
        calculateUpdateHourlyTotals();
    });
    
    // Recalculate when modal is shown (to handle data loaded via AJAX)
    $('#modalUpdate').on('shown.bs.modal', function() {
        calculateUpdateHourlyTotals();
    });
})(jQuery);
