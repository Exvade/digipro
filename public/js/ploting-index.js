/**
 * Ploting Index - Tab Filter and Modal Handler
 * Handles tab filtering for bagian (Inject/Assy) and planning detail modal
 */

// Read data from script tag data attributes
(function() {
    const scriptTag = document.querySelector('script[src*="ploting-index.js"]');
    if (scriptTag) {
        window.isLeaderInject = scriptTag.dataset.isLeaderInject === 'true';
        window.isLeaderAssy = scriptTag.dataset.isLeaderAssy === 'true';
    }
})();

$(document).ready(function() {
    // =========================================
    // Tab Filter Bagian (All, Inject, Assy) - Ploting Page
    // =========================================
    // Hanya jalankan jika tab filter ada (bukan leader dengan bagian spesifik)
    if ($('#bagianTabsPloting').length > 0) {
        $('#bagianTabsPloting .nav-link').on('click', function(e) {
            e.preventDefault();
            const bagian = $(this).data('bagian');

            // Hide all sections first
            $('.ploting-section').hide();

            // Show based on selection
            if (bagian === 'all') {
                $('.ploting-section').show();
            } else if (bagian === 'inject') {
                $('#inject-section').show();
            } else if (bagian === 'assy') {
                $('#assy-section').show();
            }

            // Update active tab
            $('#bagianTabsPloting .nav-link').removeClass('active');
            $(this).addClass('active');
        });
    } else {
        // Jika leader inject, langsung tampilkan section inject saja
        // Use window variables set above
        if (window.isLeaderInject) {
            $('#inject-section').show();
            $('#assy-section').hide();
        } else if (window.isLeaderAssy) {
            $('#inject-section').hide();
            $('#assy-section').show();
        }
    }

    // =========================================
    // Detail Modal Handler untuk Planning
    // =========================================
    $(document).on('click', '.showBtn', function() {
        const btn = $(this);
        const bagian = btn.data('bagian') || 'inject';

        // Show/hide fields based on bagian (sudah di handle di planning.js)
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
        $('#show-nama_part').text(btn.data('nama_part') || '-');
        $('#show-type').text(btn.data('type') || '-');
        $('#show-qty').text(btn.data('qty') || 0);
        $('#show-status').text(btn.data('status') || '-');
        $('#show-status-approval').text(btn.data('status_approval') || '-');
        $('#show-catatan').text(btn.data('catatan') || '-');

        // Isi data operator
        const operators = btn.data('operators') || [];
        const operatorsBody = $('#show-operators-body');
        operatorsBody.empty();

        if (operators.length > 0) {
            operators.forEach(function(op) {
                operatorsBody.append(`
                    <tr>
                        <td>${op.nama}</td>
                        <td class="text-center"><span class="badge badge-secondary">${op.grup}</span></td>
                        <td class="text-center">
                            <span class="badge ${op.status === 'on_progress' ? 'badge-info' : (op.status === 'done' ? 'badge-success' : 'badge-warning')}">
                                ${op.status === 'on_progress' ? 'On Progress' : (op.status === 'done' ? 'Done' : 'Waiting')}
                            </span>
                        </td>
                    </tr>
                `);
            });
        } else {
            operatorsBody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada operator</td>
                </tr>
            `);
        }

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
});
