$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle Good Product (OK) Click
    $('.btn-ok').click(function() {
        let btn = $(this);
        let planningId = btn.data('planning-id');

        // Disable button to prevent double click
        btn.prop('disabled', true);

        $.ajax({
            url: '/input-produksi/add-ok/' + planningId,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    updateStats(planningId, response.data);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    if (response.data.is_completed) {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Handle Direct Defect Click (Skip category selection)
    $(document).off('click', '.btn-defect-direct').on('click', '.btn-defect-direct', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let btn = $(this);
        let planningId = btn.data('planning-id');

        // Default to 'process' category for direct defect entries
        $('#selectedCategory-' + planningId).val('process');
        $('#ngPartModal-' + planningId).modal('show');
    });

    // Handle Trigger CALL (Open Global Andon Modal)
    $(document).off('click', '.btn-andon-trigger').on('click', '.btn-andon-trigger', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let btn = $(this);
        let planningId = btn.data('planning-id');
        let partName = btn.data('part-name');

        $('#andonPlanningId').val(planningId);
        $('#andonPartBadge').text(partName);
        $('#andonCallModal').modal('show');
    });

    // Handle Category Selection (From Global Andon Modal)
    $(document).off('click', '.btn-andon-select').on('click', '.btn-andon-select', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let btn = $(this);
        let category = btn.data('category');
        let planningId = $('#andonPlanningId').val();

        $('#andonCallModal').modal('hide');

        // Use higher timeout to ensure backdrop is completely gone
        setTimeout(function() {
            handleCallSupport(planningId, category);
        }, 400);
    });

    function handleCallSupport(planningId, category) {
        Swal.fire({
            title: 'Sending Call...',
            text: 'Mengirimkan sinyal panggilan bantuan (' + category.toUpperCase() + ') ke pusat kendali...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/input-produksi/add-call/' + planningId,
            type: 'POST',
            data: {
                category: category
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Call Terkirim!',
                        text: 'Leader & Tim Support telah mendapatkan notifikasi. Silakan ditunggu di area mesin.',
                        confirmButtonText: 'Siap',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload(); // Reload to show WAITING FOR SUPPORT alert
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal mengirimkan panggilan bantuan.'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi gangguan koneksi saat mengirim panggilan.'
                });
            }
        });
    }

    // Handle Defect Product (NG) Category Click
    $(document).off('click', '.btn-ng').on('click', '.btn-ng', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let link = $(this);
        let planningId = link.data('planning-id');
        let category = link.data('category');

        // Set category dan buka modal
        $('#selectedCategory-' + planningId).val(category);
        $('#ngPartModal-' + planningId).modal('show');
    });

    // Handle NG Part Selection
    $(document).off('click', '.ng-part-card').on('click', '.ng-part-card', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let card = $(this);
        let ngPartId = card.data('ng-part-id');
        let planningId = card.data('planning-id');
        let category = $('#selectedCategory-' + planningId).val();

        // Highlight selected card
        $('.ng-part-card[data-planning-id="' + planningId + '"]').removeClass('border-primary bg-light');
        card.addClass('border-primary bg-light');

        // Send AJAX request
        $.ajax({
            url: '/input-produksi/add-ng/' + planningId,
            type: 'POST',
            data: {
                category: category,
                ng_part_type_id: ngPartId
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#ngPartModal-' + planningId).modal('hide');

                    updateStats(planningId, response.data);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Defect Recorded',
                        text: response.message,
                        timer: 1000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    if (response.data.is_completed) {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }

                    // Reset card selection
                    $('.ng-part-card[data-planning-id="' + planningId + '"]').removeClass('border-primary bg-light');
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });

    // Function to update UI stats
    function updateStats(planningId, data) {
        let card = $('.production-card[data-planning-id="' + planningId + '"]');

        // Update counts (Still show day totals for general ok/ng/total labels)
        card.find('.ok-count').text(new Intl.NumberFormat().format(data.ok_count));
        card.find('.ng-count').text(new Intl.NumberFormat().format(data.ng_count));
        card.find('.total-count').text(new Intl.NumberFormat().format(data.total));
        
        // Update Target label (if it was shift target)
        card.find('.planned-qty').text(new Intl.NumberFormat().format(data.shift_target));

        // Calculate gap based on shift stats
        let gap = Math.max(0, data.shift_target - data.shift_total);
        card.find('.gap-count').text(new Intl.NumberFormat().format(gap));

        // Calculate percentage and efficiency for the shift
        let percentage = data.shift_target > 0 ? Math.min(100, Math.round((data.shift_total / data.shift_target) * 100)) : 0;
        let efficiency = data.total > 0 ? Math.round((data.ok_count / data.total) * 100) : 100;

        card.find('.efficiency-count').text(efficiency + '%');

        // Update progress bar
        let progressBar = card.find('.progress-bar');
        progressBar.css('width', percentage + '%');
        progressBar.attr('aria-valuenow', percentage);

        // Update progress text
        card.find('.progress-info').text(percentage + '%');

        // Update progress bar color
        progressBar.removeClass('bg-success bg-info bg-warning');
        if (percentage >= 100) {
            progressBar.addClass('bg-success');
        } else if (percentage >= 70) {
            progressBar.addClass('bg-info');
        } else {
            progressBar.addClass('bg-warning');
        }
    }
});
