/**
 * Laporan Produksi Index - JavaScript (Tailwind Modern UI)
 */

$(document).ready(function() {
    // Initialize DataTables
    const table = $('#tableLaporanProduksi').DataTable({
        processing: true,
        serverSide: false,
        responsive: false,
        scrollX: false,
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[1, 'desc']], // Order by tanggal descending
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            loadingRecords: "Memuat data...",
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "",
            paginate: {
                first: '<i class="fas fa-angle-double-left text-xs"></i>',
                previous: '<i class="fas fa-angle-left text-xs"></i>',
                next: '<i class="fas fa-angle-right text-xs"></i>',
                last: '<i class="fas fa-angle-double-right text-xs"></i>'
            },
        },
        columnDefs: [
            {
                orderable: false,
                targets: [0, 9] // No dan Aksi tidak bisa di-sort
            },
            {
                className: 'text-center',
                targets: [0, 2, 9] // No, Bagian, Aksi center
            },
            {
                className: 'text-right',
                targets: [5, 6, 7, 8] // Kolom angka right align
            }
        ],
        dom: "<'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4'<'w-full sm:w-auto'l><'w-full sm:w-auto'f>>" +
             "<'overflow-x-auto custom-scrollbar'tr>" +
             "<'flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800'<'text-xs text-slate-500'i><'flex items-center gap-1'p>>",
        drawCallback: function(settings) {
            // Re-initialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
            
            // Handle empty state
            if (this.api().data().length === 0) {
                let emptyRow = '<tr><td colspan="10" class="text-center py-10">' +
                    '<div class="flex flex-col items-center justify-center p-6 text-center">' +
                    '<div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 dark:text-rose-400 flex items-center justify-center text-2xl mb-3 shadow-inner">' +
                    '<i class="fas fa-inbox"></i>' +
                    '</div>' +
                    '<h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Tidak Ada Data Laporan Produksi</h4>' +
                    '<p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-4">Tidak ada data produksi yang sesuai dengan filter yang Anda tentukan atau belum ada laporan yang disetujui (approved).</p>';
                
                if ($('input[name="tanggal_awal"]').val() || $('input[name="tanggal_akhir"]').val() || $('#grup_filter').val() || $('#mesin_filter').val()) {
                    emptyRow += '<a href="' + window.location.pathname + '" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-rose-500/20 transition-all">' +
                        '<i class="fas fa-redo text-[11px]"></i><span>Reset Semua Filter</span></a>';
                }
                emptyRow += '</div></td></tr>';
                
                $(this.api().table().body()).html(emptyRow);
            }
        },
        initComplete: function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Dynamic sequence numbering for column 0 (No) on any order/search/page
    table.on('draw.dt', function () {
        var info = table.page.info();
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = (info.start + i + 1);
        });
    });

    // Handle window resize for proper column adjustments
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            table.columns.adjust();
        }, 150);
    });

    // ==========================================================================
    // KPI Cards: Counter Animation & Interactive Table Sorting / Filtering
    // ==========================================================================
    initKpiCounters();
    initKpiCardFilters(table);

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var isDecimal = $this.data('decimal') === true || $this.data('decimal') === 'true';
            var target = parseFloat($this.data('target')) || 0;
            if (target === 0) {
                $this.text(isDecimal ? '0.0' : '0');
                return;
            }

            var duration = 800;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = start + (target - start) * ease;

                if (isDecimal) {
                    $this.text(currentVal.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }));
                } else {
                    $this.text(Math.floor(currentVal).toLocaleString('id-ID'));
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    if (isDecimal) {
                        $this.text(target.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }));
                    } else {
                        $this.text(target.toLocaleString('id-ID'));
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiCardFilters(table) {
        $('.kpi-card').on('click', function() {
            var $card = $(this);
            var filter = $card.data('filter');
            var isAlreadyActive = $card.hasClass('active');

            // Reset active states across all KPI cards
            $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20 ring-sky-500/20 ring-emerald-500/20 ring-rose-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            if (isAlreadyActive && filter !== 'all') {
                // Deselect: return to default 'all' view with default sort
                var $allCard = $('.kpi-card[data-filter="all"]');
                $allCard.addClass('active ring-2 ring-indigo-500/20');
                $allCard.find('.kpi-active-indicator').removeClass('hidden');
                table.order([[1, 'desc']]).draw();
                return;
            }

            var ringClass = 'ring-indigo-500/20';
            if (filter === 'plan') ringClass = 'ring-sky-500/20';
            else if (filter === 'ok') ringClass = 'ring-emerald-500/20';
            else if (filter === 'ng') ringClass = 'ring-rose-500/20';

            $card.addClass('active ring-2 ' + ringClass);
            $card.find('.kpi-active-indicator').removeClass('hidden');

            if (filter === 'all') {
                // Default order: Tanggal & Waktu descending
                table.order([[1, 'desc']]).draw();
            } else if (filter === 'plan') {
                // Order by Qty Plan descending
                table.order([[5, 'desc']]).draw();
            } else if (filter === 'ok') {
                // Order by OK Count descending
                table.order([[6, 'desc']]).draw();
            } else if (filter === 'ng') {
                // Order by NG Count descending (prioritize defect batches)
                table.order([[7, 'desc']]).draw();
            }

            // Smooth scroll to table on smaller viewports
            if ($(window).width() < 768 && $('#tableLaporanProduksi').length > 0) {
                var targetOffset = $('#tableLaporanProduksi').closest('.bg-white, .rounded-2xl').offset();
                if (targetOffset) {
                    $('html, body').animate({
                        scrollTop: targetOffset.top - 80
                    }, 300);
                }
            }
        });
    }

    // Tab Filter for Laporan Produksi (Inject / Assy / All)
    if ($('#bagianTabsLaporan').length > 0) {
        $('#bagianTabsLaporan .nav-link').on('click', function(e) {
            e.preventDefault();
            const bagian = $(this).data('bagian');

            $('#bagianTabsLaporan .nav-link').removeClass('active');
            $(this).addClass('active');

            if (bagian === 'all') {
                table.column(2).search('').draw();
            } else if (bagian === 'assy') {
                table.column(2).search('assy|assembly', true, false).draw();
            } else {
                table.column(2).search('inject', true, false).draw();
            }
            table.columns.adjust();
        });
    } else {
        const isLeaderInject = $('#tableLaporanProduksi').data('leader-inject');
        const isLeaderAssy = $('#tableLaporanProduksi').data('leader-assy');
        if (isLeaderInject) {
            table.column(2).search('inject', true, false).draw();
        } else if (isLeaderAssy) {
            table.column(2).search('assy|assembly', true, false).draw();
        }
        table.columns.adjust();
    }

    // Quick Date Preset Buttons
    $('.btn-quick-date').on('click', function(e) {
        e.preventDefault();
        const preset = $(this).data('preset');
        const today = new Date();
        let startDate = new Date();
        let endDate = new Date();

        if (preset === 'today') {
            // startDate and endDate are already today
        } else if (preset === '7days') {
            startDate.setDate(today.getDate() - 6);
        } else if (preset === 'month') {
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        }

        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        $('input[name="tanggal_awal"]').val(formatDate(startDate));
        $('input[name="tanggal_akhir"]').val(formatDate(endDate));
    });

    // View detail modal handler
    $(document).on('click', '.btn-view-detail', function() {
        const id = $(this).data('id');
        const modal = $('#detailModal');
        const modalBody = $('#detailModalBody');
        const btnCetak = $('#btnCetakModal');
        const btnShowFull = $('#btnShowFullModal');
        const $table = $('#tableLaporanProduksi');
        
        // Show modal
        modal.modal('show');
        
        // Dynamic route URLs with fallback
        const showUrlTpl = $table.data('show-url') || '/laporan/:id';
        const cetakUrlTpl = $table.data('cetak-url') || '/laporan/:id/cetak';
        const detailUrlTpl = $table.data('detail-url') || '/laporan/:id/detail';

        // Update direct link to full show page and cetak
        if (btnShowFull.length > 0) {
            btnShowFull.attr('href', showUrlTpl.replace(':id', id));
        }
        if (btnCetak.length > 0) {
            btnCetak.attr('href', cetakUrlTpl.replace(':id', id));
        }
        
        // Reset modal body with sleek Tailwind spinner
        modalBody.html(
            '<div class="flex flex-col items-center justify-center py-12 text-center">' +
            '<div class="w-10 h-10 border-4 border-rose-500 border-t-transparent rounded-full animate-spin mb-3"></div>' +
            '<p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat detail data produksi...</p>' +
            '</div>'
        );
        
        // Load data via AJAX
        $.ajax({
            url: detailUrlTpl.replace(':id', id),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    
                    // Format Operators Chips
                    let operatorsHtml = '';
                    if (data.operators && data.operators.length > 0) {
                        data.operators.forEach(function(op) {
                            operatorsHtml += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-200 dark:border-slate-700">
                                <i class="fas fa-user-circle text-rose-500 text-[11px]"></i>
                                <span>${op}</span>
                            </span>`;
                        });
                    } else {
                        operatorsHtml = '<span class="text-xs text-slate-400">-</span>';
                    }

                    // Format NG Categories
                    let ngCategoriesHtml = '';
                    if (data.ng_categories && Object.keys(data.ng_categories).length > 0) {
                        const cats = [
                            { key: 'material', label: 'Material', icon: 'fa-cube', color: 'amber' },
                            { key: 'process', label: 'Process', icon: 'fa-microchip', color: 'blue' },
                            { key: 'machine', label: 'Machine', icon: 'fa-cogs', color: 'purple' },
                            { key: 'operator', label: 'Operator', icon: 'fa-user', color: 'indigo' },
                            { key: 'other', label: 'Lainnya', icon: 'fa-question-circle', color: 'slate' }
                        ];

                        let catBadges = '';
                        cats.forEach(function(cat) {
                            const val = data.ng_categories[cat.key] || 0;
                            if (val > 0) {
                                catBadges += `<div class="px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                                    <span class="font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                        <i class="fas ${cat.icon} text-${cat.color}-500 text-[10px]"></i>
                                        <span>${cat.label}</span>
                                    </span>
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400 ml-2">${val}</span>
                                </div>`;
                            }
                        });

                        if (catBadges) {
                            ngCategoriesHtml = `
                            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2">Kategori Defect / NG</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    ${catBadges}
                                </div>
                            </div>`;
                        }
                    }

                    // Format NG Part Types
                    let ngPartsHtml = '';
                    if (data.ng_part_types && data.ng_part_types.length > 0) {
                        let rows = '';
                        data.ng_part_types.forEach(function(ngPart) {
                            if (ngPart.quantity > 0) {
                                rows += `
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                                        ${ngPart.name}
                                        ${ngPart.code ? `<span class="text-[10px] font-mono text-slate-400 block">${ngPart.code}</span>` : ''}
                                    </td>
                                    <td class="px-3 py-2 text-center text-xs font-mono font-bold text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800">
                                        ${ngPart.quantity}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            ${ngPart.category || '-'}
                                        </span>
                                    </td>
                                </tr>`;
                            }
                        });

                        if (rows) {
                            ngPartsHtml = `
                            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2">Rincian Jenis NG Part</span>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700/80 overflow-hidden">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-slate-50 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <tr>
                                                <th class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">Jenis NG</th>
                                                <th class="px-3 py-2 text-center border-b border-slate-200 dark:border-slate-700">Qty</th>
                                                <th class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">Kategori</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${rows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;
                        }
                    }

                    // Build Main Modal Content
                    let html = `
                    <div class="space-y-4">
                        <!-- Top Metadata Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Tanggal & Waktu</span>
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                    <i class="fas fa-calendar-alt text-rose-500 text-[11px]"></i>
                                    <span>${data.tanggal}</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Mesin / Meja</span>
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                    <i class="fas fa-cog text-indigo-500 text-[11px]"></i>
                                    <span>${data.mesin}</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Customer / Mold</span>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" title="${data.customer} / ${data.mold}">
                                    <i class="fas fa-building text-blue-500 text-[11px] mr-1"></i>
                                    <span>${data.customer}</span>
                                    <span class="text-[11px] text-slate-400 font-normal">(${data.mold})</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Part</span>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" title="${data.part}">
                                    <i class="fas fa-cube text-amber-500 text-[11px] mr-1"></i>
                                    <span>${data.part}</span>
                                    ${data.part_code ? `<span class="block text-[10px] font-mono text-slate-400">${data.part_code}</span>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- KPI Highlight Badges -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/70 dark:border-indigo-800/60">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block mb-0.5">Target Plan</span>
                                <span class="text-base font-black font-mono text-indigo-900 dark:text-indigo-100">${data.qty_planning}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/70 dark:border-emerald-800/60">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-0.5">Output OK</span>
                                <span class="text-base font-black font-mono text-emerald-900 dark:text-emerald-100">${data.ok_count}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/70 dark:border-rose-800/60">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-0.5">Total NG</span>
                                <span class="text-base font-black font-mono text-rose-900 dark:text-rose-100">${data.ng_count}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 block mb-0.5">Total Produksi</span>
                                <span class="text-base font-black font-mono text-slate-900 dark:text-slate-100">${data.total}</span>
                            </div>
                        </div>

                        <!-- Operators Section -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2">Operator Terploting</span>
                            <div class="flex flex-wrap gap-1.5">
                                ${operatorsHtml}
                            </div>
                        </div>

                        <!-- NG Categories -->
                        ${ngCategoriesHtml}

                        <!-- NG Part Types Table -->
                        ${ngPartsHtml}
                    </div>
                    `;
                    
                    // Set modal body content
                    modalBody.html(html);
                    
                    // Set cetak button URL
                    btnCetak.attr('href', data.cetak_url);
                } else {
                    modalBody.html(`
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-rose-500"></i>
                            <span>Gagal memuat data detail laporan.</span>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                modalBody.html(`
                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-rose-500"></i>
                        <span>Terjadi kesalahan saat memuat data. Silakan coba lagi.</span>
                    </div>
                `);
            }
        });
    });
});
