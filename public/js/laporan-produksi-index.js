/**
 * Laporan Produksi Index - JavaScript
 */

$(document).ready(function() {
    // Initialize DataTables
    const table = $('#tableLaporanProduksi').DataTable({
        processing: true,
        serverSide: false,
        responsive: false,
        scrollX: true,
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
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            },
        },
        columnDefs: [
            {
                orderable: false,
                targets: [0, 8] // No dan Aksi tidak bisa di-sort
            },
            {
                className: 'text-center',
                targets: [0, 8] // No dan Aksi center
            },
            {
                className: 'text-right',
                targets: [4, 5, 6, 7] // Kolom angka right align
            }
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        drawCallback: function(settings) {
            // Re-initialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
            
            // Handle empty state
            if (this.api().data().length === 0) {
                let emptyRow = '<tr><td colspan="9" class="text-center py-5">' +
                    '<i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>' +
                    '<h4 class="text-muted">Tidak ada data produksi</h4>' +
                    '<p class="text-muted mb-0">Tidak ada data laporan produksi yang ditemukan.</p>';
                
                if ($('input[name="tanggal_awal"]').val() || $('input[name="tanggal_akhir"]').val()) {
                    emptyRow += '<a href="' + window.location.pathname + '" class="btn btn-sm btn-primary mt-3">' +
                        '<i class="fas fa-list mr-1"></i> Lihat Semua Data</a>';
                }
                emptyRow += '</td></tr>';
                
                $(this.api().table().body()).html(emptyRow);
            }
        },
        initComplete: function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Handle form filter submit
    $('form').on('submit', function(e) {
        // Let the form submit normally
    });

    // Auto-submit on date change (optional)
    $('input[name="tanggal_awal"], input[name="tanggal_akhir"]').on('change', function() {
        // Optional: Auto-submit when both dates are filled
        const tanggalAwal = $('input[name="tanggal_awal"]').val();
        const tanggalAkhir = $('input[name="tanggal_akhir"]').val();
        
        // Uncomment below to enable auto-submit
        // if (tanggalAwal && tanggalAkhir) {
        //     $('form').submit();
        // }
    });

    // View detail modal handler
    $(document).on('click', '.btn-view-detail', function() {
        const id = $(this).data('id');
        const modal = $('#detailModal');
        const modalBody = $('#detailModalBody');
        const btnCetak = $('#btnCetakModal');
        
        // Show modal
        modal.modal('show');
        
        // Reset modal body
        modalBody.html('<div class="text-center py-4">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Loading...</span>' +
            '</div>' +
            '<p class="mt-2 text-muted">Memuat data...</p>' +
            '</div>');
        
        // Load data via AJAX
        $.ajax({
            url: '/laporan/' + id + '/detail',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    
                    // Build HTML content
                    let html = '<div class="row">';
                    
                    // Left column
                    html += '<div class="col-md-6">';
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-calendar text-primary mr-2"></i>Tanggal</strong><br>';
                    html += '<span class="font-weight-bold">' + data.tanggal + '</span>';
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-cogs text-primary mr-2"></i>Mesin</strong><br>';
                    html += '<span class="font-weight-bold">' + data.mesin + '</span>';
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-box text-primary mr-2"></i>Part</strong><br>';
                    html += '<span class="font-weight-bold">' + data.part + '</span>';
                    if (data.part_code) {
                        html += '<br><small class="text-muted">Code: ' + data.part_code + '</small>';
                    }
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-bullseye text-primary mr-2"></i>Qty Planning</strong><br>';
                    html += '<span class="font-weight-bold">' + data.qty_planning + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Right column
                    html += '<div class="col-md-6">';
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-building text-primary mr-2"></i>Customer</strong><br>';
                    html += '<span class="font-weight-bold">' + data.customer + '</span>';
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-toolbox text-primary mr-2"></i>Mold</strong><br>';
                    html += '<span class="font-weight-bold">' + data.mold + '</span>';
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-users text-primary mr-2"></i>Operator</strong><br>';
                    if (data.operators && data.operators.length > 0) {
                        data.operators.forEach(function(operator) {
                            html += '<span class="badge badge-info mr-1 mb-1">' + operator + '</span>';
                        });
                    } else {
                        html += '<span class="text-muted">-</span>';
                    }
                    html += '</div>';
                    
                    html += '<div class="mb-3">';
                    html += '<strong><i class="fas fa-chart-bar text-primary mr-2"></i>OK / NG</strong><br>';
                    html += '<span class="badge badge-success mr-1">OK: ' + data.ok_count + '</span>';
                    html += '<span class="badge badge-danger">NG: ' + data.ng_count + '</span>';
                    html += '<br><small class="text-muted mt-1 d-block">Total: <strong>' + data.total + '</strong></small>';
                    html += '</div>';
                    
                    // NG Categories
                    if (data.ng_categories && Object.keys(data.ng_categories).length > 0) {
                        html += '<div class="mb-3">';
                        html += '<strong><i class="fas fa-exclamation-triangle text-primary mr-2"></i>NG Category</strong><br>';
                        if (data.ng_categories.material > 0) {
                            html += '<span class="badge badge-warning mr-1 mb-1">Material: ' + data.ng_categories.material + '</span>';
                        }
                        if (data.ng_categories.process > 0) {
                            html += '<span class="badge badge-info mr-1 mb-1">Process: ' + data.ng_categories.process + '</span>';
                        }
                        if (data.ng_categories.machine > 0) {
                            html += '<span class="badge badge-secondary mr-1 mb-1">Machine: ' + data.ng_categories.machine + '</span>';
                        }
                        if (data.ng_categories.operator > 0) {
                            html += '<span class="badge badge-primary mr-1 mb-1">Operator: ' + data.ng_categories.operator + '</span>';
                        }
                        if (data.ng_categories.other > 0) {
                            html += '<span class="badge badge-dark mr-1 mb-1">Other: ' + data.ng_categories.other + '</span>';
                        }
                        html += '</div>';
                    } else {
                        html += '<div class="mb-3">';
                        html += '<strong><i class="fas fa-exclamation-triangle text-primary mr-2"></i>NG Category</strong><br>';
                        html += '<span class="text-muted">No NG</span>';
                        html += '</div>';
                    }
                    
                    // NG Part Types
                    if (data.ng_part_types && data.ng_part_types.length > 0) {
                        html += '<div class="mb-3">';
                        html += '<strong><i class="fas fa-list text-primary mr-2"></i>NG Part Types</strong><br>';
                        html += '<div class="table-responsive mt-2">';
                        html += '<table class="table table-sm table-bordered">';
                        html += '<thead><tr><th>Jenis NG</th><th class="text-center">Qty</th><th>Kategori</th></tr></thead>';
                        html += '<tbody>';
                        data.ng_part_types.forEach(function(ngPart) {
                            if (ngPart.quantity > 0) {
                                html += '<tr>';
                                html += '<td>' + ngPart.name + (ngPart.code ? ' (' + ngPart.code + ')' : '') + '</td>';
                                html += '<td class="text-center">' + ngPart.quantity + '</td>';
                                html += '<td>' + (ngPart.category ? '<span class="badge badge-secondary">' + ngPart.category.charAt(0).toUpperCase() + ngPart.category.slice(1) + '</span>' : '<span class="text-muted">-</span>') + '</td>';
                                html += '</tr>';
                            }
                        });
                        html += '</tbody></table>';
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    html += '</div>';
                    
                    // Set modal body content
                    modalBody.html(html);
                    
                    // Set cetak button URL
                    btnCetak.attr('href', data.cetak_url);
                } else {
                    modalBody.html('<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle mr-2"></i>' +
                        'Gagal memuat data detail' +
                        '</div>');
                }
            },
            error: function(xhr) {
                modalBody.html('<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-circle mr-2"></i>' +
                    'Terjadi kesalahan saat memuat data. Silakan coba lagi.' +
                    '</div>');
            }
        });
    });
});
