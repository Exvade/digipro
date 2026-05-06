/**
 * Jadwal Pemeliharaan JavaScript (External)
 * Handles modal, AJAX updates, and UI interactions
 */

$(document).ready(function() {
    
    console.log('Jadwal Pemeliharaan JS Loaded');

    // CSRF Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 1. Event Delegation for Cell Clicking (Targeting TD directly)
    // =========================================
    $(document).on('click', 'td.edit-trigger', function(e) {
        // Mencegah klik ganda jika elemen di dalam TD juga diklik
        e.stopPropagation();

        const $el = $(this);
        console.log('Cell Clicked:', $el.data('type'), 'for Mesin ID:', $el.data('mesin-id'));
        
        // Ambil data dari atribut data- jQuery .data()
        const data = {
            mesinId: $el.data('mesin-id'),
            mesinNama: $el.data('mesin-nama'),
            bulan: $el.data('bulan'),
            minggu: $el.data('minggu'),
            typeClick: $el.data('type'),
            currentPlan: $el.data('plan'),
            currentActual: $el.data('actual'),
            keterangan: $el.data('keterangan')
        };
        
        // Validasi data dasar
        if (!data.mesinId) {
            console.warn('Click ignored: Missing Mesin ID');
            return;
        }

        openEditModal(data);
    });

    // 2. Modal Functions
    // =========================================

    function openEditModal(data) {
        const $modal = $('#modalEditJadwal');
        if (!$modal.length) {
            console.error('Modal #modalEditJadwal not found in DOM');
            return;
        }

        // Populate Info Labels
        $('#labelMesin').text(data.mesinNama);
        $('#labelBulan').text(data.bulan);
        $('#labelMinggu').text(data.minggu);
        
        // Populate Hidden Inputs
        $('#inputMesinId').val(data.mesinId);
        $('#inputBulan').val(data.bulan);
        $('#inputMinggu').val(data.minggu);
        
        // Set Values for Plan
        $('#inputPlan').val(data.currentPlan || '');
        if ($('#inputPlanDisplay').length) {
            $('#inputPlanDisplay').val(data.currentPlan || 'Kosong');
        }
        
        // Set Values for Actual
        $('#inputActual').val(data.currentActual || '');
        if ($('#inputActualDisplay').length) {
            $('#inputActualDisplay').val(data.currentActual || 'Belum Terisi');
        }

        // Tampilan Section Berdasarkan Konteks Klik
        const $sectionPlan = $('#sectionPlan');
        const $sectionActual = $('#sectionActual');
        const $modalTitle = $modal.find('.modal-title');

        if (data.typeClick === 'plan') {
            $sectionPlan.show().removeClass('col-md-6').addClass('col-md-12');
            $sectionActual.hide();
            $modalTitle.html(`<i class="fas fa-calendar-alt"></i> Update Planning (Bulan ${data.bulan} / Minggu ${data.minggu})`);
            
            setTimeout(() => {
                const $input = $('#inputPlan');
                if ($input.is(':visible') && $input.attr('type') !== 'hidden') {
                    $input.focus().select();
                }
            }, 400);
        } else {
            $sectionActual.show().removeClass('col-md-6').addClass('col-md-12');
            $sectionPlan.hide();
            $modalTitle.html(`<i class="fas fa-check-circle"></i> Update Actual (Bulan ${data.bulan} / Minggu ${data.minggu})`);
            
            setTimeout(() => {
                const $input = $('#inputActual');
                if ($input.is(':visible') && $input.attr('type') !== 'hidden') {
                    $input.focus().select();
                }
            }, 400);
        }

        $modal.modal('show');
    }

    // 3. Save Function (Global scope for button onclick)
    // =========================================

    window.saveJadwal = function() {
        const updateUrl = window.urlUpdateJadwal;
        const $form = $('#formUpdateJadwal');
        const $btnSave = $('button[onclick^="saveJadwal"]');

        if (!$form.length || !updateUrl) {
            console.error('Form or Update URL missing');
            return;
        }

        // Feedback loading
        $btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    updateTableUI(response.data);
                    $('#modalEditJadwal').modal('hide');
                } else {
                    alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                alert('Gagal menghubungi server. Periksa koneksi Anda.');
            },
            complete: function() {
                $btnSave.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Data');
            }
        });
    };

    /**
     * Update UI tabel secara dinamis mengikuti skema TD-based
     */
    function updateTableUI(data) {
        const { mesin_id, bulan, minggu, plan_tipe, aktual_tanggal } = data;
        const planVal = plan_tipe || '';
        const actualVal = aktual_tanggal || '';

        // Cari elemen berdasarkan data attributes (lebih akurat)
        const $targetPlan = $(`td.edit-trigger[data-mesin-id="${mesin_id}"][data-bulan="${bulan}"][data-minggu="${minggu}"][data-type="plan"]`);
        const $targetActual = $(`td.edit-trigger[data-mesin-id="${mesin_id}"][data-bulan="${bulan}"][data-minggu="${minggu}"][data-type="actual"]`);

        if ($targetPlan.length) {
            const html = planVal ? `<div style="background-color: #2e7d32; color: white; width: 85%; height: 85%; border-radius: 2px;" class="d-flex align-items-center justify-content-center cell-text-plan shadow-sm mx-auto mt-1">${planVal}</div>` : '';
            $targetPlan.html(html);
            $targetPlan.data('plan', planVal); // Update internal state
            $targetPlan.attr('data-plan', planVal); // Update DOM attribute for persistence
        }
        
        if ($targetActual.length) {
            $targetActual.find('.cell-text-actual').text(actualVal);
            $targetActual.data('actual', actualVal);
            $targetActual.attr('data-actual', actualVal);
        }
    }
});
