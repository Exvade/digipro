/**
 * Admin Documents Page JavaScript - Modernized for DIGIPRO Tailwind & DataTables
 */

$(document).ready(function() {
    // Update custom file input label when file is selected
    $(document).on('change', '.custom-file-input', function() {
        var fileName = $(this).val().split('\\').pop();
        var $label = $(this).siblings('.custom-file-label');
        if (fileName) {
            $label.addClass('text-slate-800 dark:text-slate-200 font-semibold').html('<i class="fas fa-file-check text-emerald-500 mr-1.5"></i>' + fileName);
        } else {
            $label.removeClass('text-slate-800 dark:text-slate-200 font-semibold').html('Pilih file...');
        }
    });

    // Disable form submission for maintenance/inactive machines
    $('form.upload-form').on('submit', function(e) {
        var $form = $(this);
        var isDisabled = $form.closest('tr').hasClass('machine-disabled');
        
        if (isDisabled) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Upload Dibatalkan',
                    text: 'Mesin dengan status Breakdown / Stand By / Off tidak dapat mengupload dokumen. Upload hanya diizinkan saat status RUNNING.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'rounded-xl font-bold text-xs px-4 py-2.5'
                    }
                });
            } else {
                alert('Upload hanya diizinkan saat mesin berstatus RUNNING.');
            }
            return false;
        }
    });

    // Konfigurasi DataTable Modern
    var dataTableConfig = {
        language: {
            "emptyTable": "Tidak ada data dokumen yang tersedia",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 data",
            "infoFiltered": "(disaring dari _MAX_ total data)",
            "lengthMenu": "Tampilkan _MENU_ data",
            "loadingRecords": "Memuat data...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "searchPlaceholder": "Cari nama mesin/meja...",
            "zeroRecords": "Tidak ditemukan data yang sesuai",
            "paginate": {
                "first": '<i class="fas fa-angle-double-left text-xs"></i>',
                "last": '<i class="fas fa-angle-double-right text-xs"></i>',
                "next": '<i class="fas fa-angle-right text-xs"></i>',
                "previous": '<i class="fas fa-angle-left text-xs"></i>'
            }
        },
        paging: true,
        pagingType: 'full_numbers',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        lengthChange: true,
        info: true,
        ordering: false,
        searching: true,
        responsive: false,
        autoWidth: false,
        dom: "<'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4'<'w-full sm:w-auto'l><'w-full sm:w-auto'f>>" +
             "<'overflow-x-auto custom-scrollbar'tr>" +
             "<'flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800'<'text-xs font-medium text-slate-500 dark:text-slate-400'i><'flex items-center gap-1 overflow-x-auto max-w-full custom-scrollbar'p>>",
        drawCallback: function() {
            // Re-bind file inputs after page change
            $('.custom-file-input').off('change').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                var $label = $(this).siblings('.custom-file-label');
                if (fileName) {
                    $label.addClass('text-slate-800 dark:text-slate-200 font-semibold').html('<i class="fas fa-file-check text-emerald-500 mr-1.5"></i>' + fileName);
                } else {
                    $label.removeClass('text-slate-800 dark:text-slate-200 font-semibold').html('Pilih file...');
                }
            });
        }
    };

    // Initialize DataTable untuk Mesin
    var mesinDt = null;
    if ($('#mesin-table').length) {
        if ($.fn.dataTable.isDataTable('#mesin-table')) {
            $('#mesin-table').DataTable().destroy();
        }
        mesinDt = $('#mesin-table').DataTable(dataTableConfig);
    }

    // Initialize DataTable untuk Meja
    var mejaDt = null;
    if ($('#meja-table').length) {
        if ($.fn.dataTable.isDataTable('#meja-table')) {
            $('#meja-table').DataTable().destroy();
        }
        mejaDt = $('#meja-table').DataTable(dataTableConfig);
    }

    // ==========================================================================
    // KPI Cards: Counter Animation & Interactive Table Filtering
    // ==========================================================================
    initKpiCounters();
    initKpiCardFilters();

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var target = parseInt($this.data('target'), 10) || 0;
            if (target === 0) {
                $this.text('0');
                return;
            }

            var duration = 750;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = Math.floor(start + (target - start) * ease);

                $this.text(currentVal.toLocaleString());

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    $this.text(target.toLocaleString());
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiCardFilters() {
        var activeCustomFilter = null;

        function setCustomFilter(fn) {
            clearCustomFilter();
            activeCustomFilter = fn;
            $.fn.dataTable.ext.search.push(activeCustomFilter);
        }

        function clearCustomFilter() {
            if (activeCustomFilter) {
                var idx = $.fn.dataTable.ext.search.indexOf(activeCustomFilter);
                if (idx !== -1) {
                    $.fn.dataTable.ext.search.splice(idx, 1);
                }
                activeCustomFilter = null;
            }
        }

        $('.kpi-card').on('click', function() {
            var $card = $(this);
            var filter = $card.data('filter');
            var isAlreadyActive = $card.hasClass('active');

            // Reset active state across all cards
            $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            if (isAlreadyActive && filter !== 'all-mesin') {
                // If clicking active filter card again, reset back to all machines
                $('.kpi-card[data-filter="all-mesin"]').addClass('active ring-2 ring-indigo-500/20');
                $('.kpi-card[data-filter="all-mesin"] .kpi-active-indicator').removeClass('hidden');
                clearCustomFilter();
                $('a[href="#tab-mesin"]').tab('show');
                if (mesinDt) mesinDt.draw();
                return;
            }

            // Set current card active
            $card.addClass('active ring-2 ring-indigo-500/20');
            $card.find('.kpi-active-indicator').removeClass('hidden');

            if (filter === 'all-mesin') {
                $('a[href="#tab-mesin"]').tab('show');
                clearCustomFilter();
                if (mesinDt) mesinDt.draw();
            } else if (filter === 'running') {
                $('a[href="#tab-mesin"]').tab('show');
                if (mesinDt) {
                    setCustomFilter(function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'mesin-table') return true;
                        var row = mesinDt.row(dataIndex).node();
                        return $(row).attr('data-status') === 'running';
                    });
                    mesinDt.draw();
                }
            } else if (filter === 'standby-breakdown') {
                $('a[href="#tab-mesin"]').tab('show');
                if (mesinDt) {
                    setCustomFilter(function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'mesin-table') return true;
                        var row = mesinDt.row(dataIndex).node();
                        return $(row).attr('data-status') !== 'running';
                    });
                    mesinDt.draw();
                }
            } else if (filter === 'all-meja') {
                $('a[href="#tab-meja"]').tab('show');
                clearCustomFilter();
                if (mejaDt) mejaDt.draw();
            }
        });

        // Tab switch listeners to sync active KPI card
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('href');
            if (target === '#tab-meja') {
                if (!$('.kpi-card[data-filter="all-meja"]').hasClass('active')) {
                    $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20');
                    $('.kpi-card .kpi-active-indicator').addClass('hidden');
                    $('.kpi-card[data-filter="all-meja"]').addClass('active ring-2 ring-indigo-500/20');
                    $('.kpi-card[data-filter="all-meja"] .kpi-active-indicator').removeClass('hidden');
                }
            } else if (target === '#tab-mesin') {
                if ($('.kpi-card[data-filter="all-meja"]').hasClass('active')) {
                    $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20');
                    $('.kpi-card .kpi-active-indicator').addClass('hidden');
                    $('.kpi-card[data-filter="all-mesin"]').addClass('active ring-2 ring-indigo-500/20');
                    $('.kpi-card[data-filter="all-mesin"] .kpi-active-indicator').removeClass('hidden');
                    clearCustomFilter();
                    if (mesinDt) mesinDt.draw();
                }
            }
        });
    }

    // Handle tab switch to redraw tables
    $('a[data-toggle="tab"], button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
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
        $('#documentViewerModal .modal-title-text').text(docName);
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
