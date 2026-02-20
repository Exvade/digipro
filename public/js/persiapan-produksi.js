// Persiapan Produksi Modal Handler
$(document).ready(function() {
    // Auto-open modal logic - will be triggered by inline script with planning IDs
    window.openPersiapanModals = function(planningIds) {
        planningIds.forEach(function(planningId) {
            $('#persiapanModal-' + planningId).modal('show');
        });
    };

    // Load persiapan data
    $('.btn-save-persiapan').each(function() {
        let plotingId = $(this).data('ploting-id');
        let planningId = $(this).data('planning-id');
        let operatorShift = $(this).data('operator-shift');
        loadPersiapan(plotingId, planningId, operatorShift);
    });

    // Save persiapan
    $(document).on('click', '.btn-save-persiapan', function() {
        let btn = $(this);
        let plotingId = btn.data('ploting-id');
        let planningId = btn.data('planning-id');
        let operatorShift = btn.data('operator-shift'); // Get operator's shift
        let items = [];

        // Validate: Check if at least one item is checked
        let hasChecked = false;
        $('#persiapanTable-' + planningId + ' tbody tr').each(function() {
            let row = $(this);
            if (row.find('.check-' + operatorShift).is(':checked')) {
                hasChecked = true;
                return false; // break loop
            }
        });

        if (!hasChecked) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Silakan centang minimal satu item persiapan produksi'
            });
            return;
        }

        $('#persiapanTable-' + planningId + ' tbody tr').each(function() {
            let row = $(this);
            let itemName = row.data('item');
            let keterangan = row.find('.input-keterangan').val() || '';

            // Only save for operator's shift
            items.push({
                item_name: itemName + ' (' + operatorShift.charAt(0).toUpperCase() + operatorShift.slice(1) + ')',
                operator_checked: row.find('.check-' + operatorShift).is(':checked') ? 1 : 0,
                leader_checked: 0,
                keterangan: keterangan
            });
        });

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: '/input-produksi/persiapan/' + plotingId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                items: items
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data persiapan produksi berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Reload page to show input production buttons
                    location.reload();
                });
            },
            error: function(xhr) {
                console.error(xhr);
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMessage = xhr.statusText;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal (' + xhr.status + ')',
                    text: errorMessage
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Persiapan');
            }
        });
    });

    function loadPersiapan(plotingId, planningId, operatorShift) {
        $.get('/input-produksi/persiapan/' + plotingId, function(data) {
            if (data && data.length > 0) {
                data.forEach(function(item) {
                    let baseItemName = item.item_name;
                    let shift = '';

                    if (baseItemName.endsWith(' (Pagi)')) {
                        baseItemName = baseItemName.replace(' (Pagi)', '');
                        shift = 'pagi';
                    } else if (baseItemName.endsWith(' (Siang)')) {
                        baseItemName = baseItemName.replace(' (Siang)', '');
                        shift = 'siang';
                    } else if (baseItemName.endsWith(' (Malam)')) {
                        baseItemName = baseItemName.replace(' (Malam)', '');
                        shift = 'malam';
                    }

                    let row = $('#persiapanTable-' + planningId + ' tbody tr').filter(function() {
                        return $(this).data('item') === baseItemName;
                    });

                    if (row.length > 0 && shift === operatorShift) {
                        // Only load data for operator's shift
                        row.find('.check-' + shift).prop('checked', item.operator_checked);
                        
                        // Update keterangan
                        if (item.keterangan) {
                            row.find('.input-keterangan').val(item.keterangan);
                        }
                    }
                });
            }
        });
    }
});
