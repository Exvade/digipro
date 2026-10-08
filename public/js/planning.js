/**
 * Planning Module Consolidated JavaScript
 * Handles Index, History, Approval and Common functionalities
 */

(function($) {
    'use strict';

    // =========================================
    // 1. Initialization & CSRF Setup
    // =========================================
    function init() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
        }

        const dataScript = document.getElementById('planning-data');
        if (dataScript) {
            try {
                const data = JSON.parse(dataScript.textContent);
                window.successMessage = data.successMessage;
                window.planningIndexUrl = data.indexUrl || '';
                window.planningCetakUrl = data.cetakUrl || '';
                window.todayYmd = data.todayYmd || '';
                window.tomorrowYmd = data.tomorrowYmd || '';
                window.currentFilter = data.currentFilter;
            } catch (e) {
                console.error('Error parsing planning data:', e);
            }
        }
    }

    $(document).ready(function() {
        init();
        initDarkMode();
        
        // Custom File Input Label Change
        $('#excel_file').on('change', function(e) {
            if (e.target.files.length > 0) {
                $('.custom-file-label[for="excel_file"]').text(e.target.files[0].name);
            } else {
                $('.custom-file-label[for="excel_file"]').text('Pilih file...');
            }
        });
        
        // DataTables
        const dTableConfig = { 
            "paging": false, 
            "lengthChange": false, 
            "searching": true, 
            "ordering": true, 
            "info": false, 
            "autoWidth": false,
            "language": {
                "emptyTable": "Belum ada data planning produksi untuk tanggal ini",
                "zeroRecords": "Tidak ada data yang sesuai dengan pencarian",
                "search": "Cari:",
                "infoEmpty": "Tidak ada data"
            }
        };
        
        let planningTable = $('#planningTable').length ? $('#planningTable').DataTable(dTableConfig) : null;
        let historyTable = $('#historyTable').length ? $('#historyTable').DataTable(dTableConfig) : null;
        let approvalTable = $('#approvalTable').length ? $('#approvalTable').DataTable(dTableConfig) : null;

        // Tab Filters
        const filterFn = (table, bagian) => {
            if (!table) return;
            const targetCol = (table.table().node().id === 'approvalTable') ? 4 : 3;
            if (bagian === 'all') table.search('').columns().search('').draw();
            else if (bagian === 'inject') table.column(targetCol).search('Inject', true, false).draw();
            else if (bagian === 'assy') table.column(targetCol).search('Assembly', true, false).draw();
        };

        $('.bagian-tabs .nav-link').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            const parentId = btn.closest('.bagian-tabs').attr('id');
            const targetTable = parentId === 'bagianTabs' ? planningTable : (parentId === 'bagianTabsHistory' ? historyTable : approvalTable);
            
            filterFn(targetTable, btn.data('bagian'));
            $(`#${parentId} .nav-link`).removeClass('active');
            btn.addClass('active');
        });

        // Date Filter (Index)
        const $select = $('#filterTanggalIndex');
        const $customDate = $('#customDateIndex');
        if ($select.length) {
            if (window.currentFilter === window.todayYmd) $select.val('today');
            else if (window.currentFilter === window.tomorrowYmd) $select.val('tomorrow');
            else if (window.currentFilter) { $select.val('custom'); $customDate.val(window.currentFilter).show(); }

            $select.on('change', function() {
                const v = $(this).val();
                if (v === 'custom') $customDate.show().focus();
                else window.location.href = window.planningIndexUrl + '?tanggal=' + encodeURIComponent(v === 'today' ? window.todayYmd : window.tomorrowYmd);
            });
            $customDate.on('change', function() {
                window.location.href = window.planningIndexUrl + '?tanggal=' + encodeURIComponent($(this).val());
            });
        }

        // Modals
        $(document).on('click', '.showBtn', function() { populateShowModal($(this)); $('#modalShow').modal('show'); });
        
        $(document).on('click', '.editBtn', function() {
            const btn = $(this);
            const id = btn.data('id');
            const bagian = btn.data('bagian') || 'inject';
            $('#updateForm')[0].reset();
            $('#update_bagian').val(bagian);
            
            if (bagian === 'inject') {
                $('#update-mold-mesin-card').show(); $('#update-meja-card').hide();
                $('#update_moldshop_id, #update_mesin_id').prop('required', true).removeAttr('disabled').attr('name', (i,v) => v === 'moldshop_id' ? 'moldshop_id' : 'mesin_id');
                $('#update_meja_id').prop('required', false).attr('disabled', 'disabled').removeAttr('name');
            } else {
                $('#update-mold-mesin-card').hide(); $('#update-meja-card').show();
                $('#update_moldshop_id, #update_mesin_id').prop('required', false).attr('disabled', 'disabled').removeAttr('name');
                $('#update_meja_id').prop('required', true).removeAttr('disabled').attr('name', 'meja_id');
            }

            setTimeout(() => {
                if (bagian === 'inject') { $('#update_moldshop_id').val(btn.data('moldshop_id')).trigger('change'); $('#update_mesin_id').val(btn.data('mesin_id')).trigger('change'); }
                else { $('#update_meja_id').val(btn.data('meja_id')).trigger('change'); }
                $('#update_part_id').val(btn.data('part_id')).trigger('change');
                $('#update_tanggal').val(btn.data('tanggal'));
                $('#update_lot_produksi').val(btn.data('lot_produksi'));
                $('#update_qty_planning_produksi').val(btn.data('qty'));
                $('#update_catatan').val(btn.data('catatan'));
            }, 200);

            $('#update_target_shift_pagi').val(btn.data('target_shift_pagi'));
            $('#update_target_shift_siang').val(btn.data('target_shift_siang'));
            $('#update_target_shift_malam').val(btn.data('target_shift_malam'));
            $('#update_total_shift').val(btn.data('total_shift'));

            const jData = btn.data('jam') || [];
            const jMap = {};
            jData.forEach(it => { const h = parseInt(it.jam.split(':')[0]); const s = (it.shift || it[' shift'] || '').trim().toLowerCase(); jMap[s + '_' + h] = it.target; });
            for (let i = 7; i <= 14; i++) $('#update_pagi_jam_' + String(i).padStart(2,'0')).val(jMap['pagi_' + i] || 0);
            for (let i = 15; i <= 22; i++) $('#update_siang_jam_' + String(i).padStart(2,'0')).val(jMap['siang_' + i] || 0);
            [23,0,1,2,3,4,5,6].forEach(i => $('#update_malam_jam_' + String(i).padStart(2,'0')).val(jMap['malam_' + i] || 0));

            $('#updateForm').attr('action', (window.planningIndexUrl || '/planning') + '/' + id);
            $('#modalUpdate').modal('show');
        });

        $(document).on('click', '.deleteForm button', function(e) {
            e.preventDefault();
            const f = $(this).closest('form');
            Swal.fire({ title: 'Yakin hapus?', text: 'Data tidak bisa dikembalikan!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus' }).then((r) => { if (r.isConfirmed) f[0].submit(); });
        });

        // Approval Buttons
        $(document).on('click', '.approveBtn', function() {
            const b = $(this);
            $('#approval_planning_id').val(b.data('id'));
            $('#approval-no_part').text(b.data('no_part'));
            $('#approval-nama_part').text(b.data('nama_part'));
            $('#approval-qty').text(b.data('qty'));
            $('#approval_action').val('approve');
            $('#approveContent').show(); $('#rejectContent').hide();
            $('#modalApproval').modal('show');
        });

        $(document).on('click', '.rejectBtn', function() {
            const id = $(this).data('id');
            $('#reject_planning_id').val(id);
            $('#rejection_reason').val('');
            $('#modalReject').modal('show');
        });

        $(document).on('click', '.viewBtn', function() {
            const id = $(this).data('id');
            $.ajax({
                url: (window.planningIndexUrl || '/planning') + '/' + id,
                method: 'GET',
                success: (res) => { populateShowModalWithData(res); $('#modalShow').modal('show'); },
                error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal muat data.' })
            });
        });

        if (window.successMessage) Swal.fire({ icon: 'success', title: 'Berhasil', text: window.successMessage, showConfirmButton: false, timer: 2000 });
    });

    function populateShowModal(btn) {
        const d = { 
            bagian: btn.data('bagian'), no_mold: btn.data('no_mold'), mesin: btn.data('mesin'), meja: btn.data('meja'), 
            tanggal: btn.data('tanggal'), lot: btn.data('lot'), no_part: btn.data('no_part'), no_barang: btn.data('no_barang'), 
            nama_part: btn.data('nama_part'), type: btn.data('type'), qty: btn.data('qty'), catatan: btn.data('catatan'), 
            status_approval: btn.data('status_approval'), target_shift_pagi: btn.data('target_shift_pagi'), 
            target_shift_siang: btn.data('target_shift_siang'), target_shift_malam: btn.data('target_shift_malam'), total_shift: btn.data('total_shift') 
        };
        let jam = btn.data('jam'); if (typeof jam === 'string') try { jam = JSON.parse(jam); } catch(e) { jam = []; }
        (jam || []).forEach(it => { const h = parseInt(it.jam.split(':')[0]); const s = (it.shift || it[' shift'] || '').trim().toLowerCase(); d[s + '_' + h] = it.target; });
        populateShowModalWithData(d);
    }

    function populateShowModalWithData(d) {
        const isAssy = d.bagian === 'assy';
        $('#show-bagian-badge').text(isAssy ? 'Assembly' : 'Inject').attr('class', isAssy ? 'badge badge-info' : 'badge badge-primary');
        $('#row-no_mold, #row-mesin').toggle(!isAssy); $('#row-meja').toggle(isAssy);
        $('#show-meja').text(d.meja || '-'); $('#show-no_mold').text(d.no_mold || '-'); $('#show-mesin').text(d.mesin || '-');

        const fs = ['tanggal', 'lot', 'no_part', 'no_barang', 'nama_part', 'type', 'qty', 'catatan', 'target_shift_pagi', 'target_shift_siang', 'target_shift_malam', 'total_shift'];
        fs.forEach(f => $('#show-' + f).text(d[f] || (f.includes('qty') || f.includes('target') ? 0 : '-')));

        const st = d.status_approval || 'pending';
        const m = { 'approved': 'badge-success', 'rejected': 'badge-danger', 'pending': 'badge-warning' };
        $('#show-status-approval').html(`<span class="badge ${m[st] || 'badge-secondary'}">${st.toUpperCase()}</span>`);

        $('[id^="pagi_jam_"], [id^="siang_jam_"], [id^="malam_jam_"]').text(0);
        let tP = 0, tS = 0, tM = 0;
        for (let i = 7; i <= 14; i++) { let v = parseInt(d['pagi_'+i] || d['pagi_jam_'+String(i).padStart(2,'0')] || 0); $('#pagi_jam_'+String(i).padStart(2,'0')).text(v); tP += v; }
        for (let i = 15; i <= 22; i++) { let v = parseInt(d['siang_'+i] || d['siang_jam_'+String(i).padStart(2,'0')] || 0); $('#siang_jam_'+String(i).padStart(2,'0')).text(v); tS += v; }
        [23,0,1,2,3,4,5,6].forEach(i => { let v = parseInt(d['malam_'+i] || d['malam_jam_'+String(i).padStart(2,'0')] || 0); $('#malam_jam_'+String(i).padStart(2,'0')).text(v); tM += v; });
        $('#total-pagi').text(tP); $('#total-siang').text(tS); $('#total-malam').text(tM);
        $('#show-grandtotal').text(new Intl.NumberFormat('id-ID').format(tP + tS + tM));
    }

    function initDarkMode() {
        const sw = document.getElementById('darkModeSwitch'), icon = document.getElementById('darkModeIcon');
        if (!sw || !icon) return;
        const en = () => { document.body.classList.add('dark-mode'); sw.checked = true; icon.classList.replace('fa-moon', 'fa-sun'); localStorage.setItem('darkMode', 'enabled'); };
        const di = () => { document.body.classList.remove('dark-mode'); sw.checked = false; icon.classList.replace('fa-sun', 'fa-moon'); localStorage.setItem('darkMode', 'disabled'); };
        if (localStorage.getItem('darkMode') === 'enabled') en();
        sw.addEventListener('change', () => sw.checked ? en() : di());
    }

    window.submitApproval = function(batchIds = null) {
        const isReject = $('#modalReject').hasClass('show');
        const planningId = isReject ? $('#reject_planning_id').val() : $('#approval_planning_id').val();
        const action = isReject ? 'reject' : $('#approval_action').val();
        const reason = $('#rejection_reason').val();

        if (action === 'reject' && (!reason || !reason.trim())) {
            return Swal.fire({ icon: 'error', title: 'Error', text: 'Alasan penolakan wajib diisi!' });
        }

        const data = { action: action, rejection_reason: reason };
        if (batchIds) {
            data.planning_ids = batchIds;
        } else {
            data.planning_id = planningId;
        }

        const btn = isReject ? $('#btnConfirmReject') : $('#submitApprovalBtn');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>...');
        
        $.ajax({ 
            url: '/planning/approval', 
            method: 'POST', 
            data: data, 
            success: (r) => { 
                if (r.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: r.message, timer: 1500 });
                    
                    // Remove processed rows from table
                    const idsToRemove = batchIds || [planningId];
                    idsToRemove.forEach(id => {
                        $(`#row-${id}`).fadeOut(400, function() { 
                            $(this).remove();
                            updateBulkActionUI();
                            // If table is empty, show alert
                            if ($('#approvalTable tbody tr').length === 0) {
                                location.reload(); // Just reload if empty to show the "no data" alert
                            }
                        });
                    });

                    $('#modalApproval').modal('hide');
                    $('#modalReject').modal('hide');
                } else { 
                    Swal.fire({ icon: 'error', text: r.message }); 
                } 
                btn.prop('disabled', false).html(originalHtml);
            },
            error: (x) => { 
                Swal.fire({ icon: 'error', text: x.responseJSON?.message || 'Gagal.' }); 
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    };

    $(document).on('click', '#submitApprovalBtn, #btnConfirmReject', function() {
        window.submitApproval();
    });

    // Batch Approval Logic
    $(document).on('change', '#checkAll', function() {
        $('.planning-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionUI();
    });

    $(document).on('change', '.planning-checkbox', function() {
        if ($('.planning-checkbox:checked').length === $('.planning-checkbox').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
        updateBulkActionUI();
    });

    function updateBulkActionUI() {
        const checkedCount = $('.planning-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#selectedCount').text(checkedCount);
            $('#bulkActionGroup').fadeIn();
        } else {
            $('#bulkActionGroup').fadeOut();
            $('#checkAll').prop('checked', false);
        }
    }

    $(document).on('click', '#btnBulkApprove', function() {
        const selectedIds = $('.planning-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Approve Masal?',
            text: `Anda akan menyetujui ${selectedIds.length} planning sekaligus.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Approve Semua!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.submitApproval(selectedIds);
            }
        });
    });

    window.submitCetak = function() {
        const t = $('#tanggalFilter').val(), c = $('#customDate').val(), b = $('#bagianFilter').val();
        if (t === 'custom' && !c) return alert('Pilih tanggal!');
        window.open((window.planningCetakUrl || '/planning/cetak') + "?tanggal=" + (t === 'custom' ? c : t) + "&bagian=" + b, '_blank');
        $('#modalCetak').modal('hide');
    };
    $(document).on('change', '#tanggalFilter', function() { $('#customDateGroup').toggle($(this).val() === 'custom'); });
})(jQuery);
