let pendingData = [];
let approvedData = [];
let rejectedData = [];
let visiblePendingData = [];
let visibleApprovedData = [];
let visibleRejectedData = [];
let currentBagianFilter = 'all';
let currentStartDate = $('#start_date').val() || new Date().toISOString().split('T')[0];
let currentEndDate = $('#end_date').val() || new Date().toISOString().split('T')[0];


const checklistItems = [
    'Briefing dan tukar informasi',
    'Kesiapan Produk WIP & Subpart',
    'Kesiapan Packing',
    'Meja proses tersedia, lampu penerangan menyala',
    'Kelengkapan dokumen IK, Q-Point, dan Std Packing',
    'Pengecekan 5R',
    'Pemeriksaan kondisi subpart (sesuai dengan planning, CKD, CBU, Reguler)',
    'Kesiapan Tag Label, Marker, dan Cutter',
    'Ketersediaan limit sampel produk'
];

// Data grouped by shift
let pendingByShift = {
    shift_1: [],
    shift_2: [],
    shift_3: []
};
let approvedByShift = {
    shift_1: [],
    shift_2: [],
    shift_3: []
};

// DataTables instances
let pendingTableShift1Table = null;
let pendingTableShift2Table = null;
let pendingTableShift3Table = null;
let approvedTableShift1Table = null;
let approvedTableShift2Table = null;
let approvedTableShift3Table = null;
let rejectedTable = null;

// DataTables configuration
const dataTableConfig = {
    pageLength: 10,
    lengthChange: true,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    ordering: false,
    info: true,
    searching: true,
    responsive: false,
    scrollX: true,
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        infoFiltered: '(difilter dari _MAX_ total data)',
        paginate: {
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Selanjutnya',
            previous: 'Sebelumnya'
        },
        emptyTable: '', // Empty string to prevent default message
        zeroRecords: 'Tidak ada data yang cocok dengan pencarian'
    },
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    columnDefs: [
        { orderable: false, targets: -1 } // Disable sorting on last column (Aksi)
    ]
};

$(document).ready(function() {
    loadAllData();
    
    // Auto refresh setiap 30 detik
    setInterval(loadAllData, 30000);
    
    // Handle tab change to initialize DataTables for visible tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        
        // Initialize DataTable for the active tab if not already initialized
        if (target === '#pending') {
            // Initialize DataTables for all shift tables
            if (!pendingTableShift1Table) {
                const config1 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                            $('#pendingTableShift1 tbody').html(emptyRow);
                        }
                    }
                };
                pendingTableShift1Table = $('#pendingTableShift1').DataTable(config1);
            }
            if (!pendingTableShift2Table) {
                const config2 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                            $('#pendingTableShift2 tbody').html(emptyRow);
                        }
                    }
                };
                pendingTableShift2Table = $('#pendingTableShift2').DataTable(config2);
            }
            if (!pendingTableShift3Table) {
                const config3 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                            $('#pendingTableShift3 tbody').html(emptyRow);
                        }
                    }
                };
                pendingTableShift3Table = $('#pendingTableShift3').DataTable(config3);
            }
        } else if (target === '#approved') {
            // Initialize DataTables for all shift tables
            if (!approvedTableShift1Table) {
                const config1 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                            const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                            $('#approvedTableShift1 tbody').html(emptyRow);
                        }
                    }
                };
                approvedTableShift1Table = $('#approvedTableShift1').DataTable(config1);
            }
            if (!approvedTableShift2Table) {
                const config2 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                            const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                            $('#approvedTableShift2 tbody').html(emptyRow);
                        }
                    }
                };
                approvedTableShift2Table = $('#approvedTableShift2').DataTable(config2);
            }
            if (!approvedTableShift3Table) {
                const config3 = {
                    ...dataTableConfig,
                    drawCallback: function(settings) {
                        if (this.api().data().length === 0) {
                            const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                            $('#approvedTableShift3 tbody').html(emptyRow);
                        }
                    }
                };
                approvedTableShift3Table = $('#approvedTableShift3').DataTable(config3);
            }
        } else if (target === '#rejected' && !rejectedTable) {
            const rejectedConfig = {
                ...dataTableConfig,
                drawCallback: function(settings) {
                    if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="15" class="text-center text-muted py-4">' +
                            '<i class="fas fa-times-circle fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data rejected</h5></td></tr>';
                        $('#rejectedTable tbody').html(emptyRow);
                    }
                }
            };
            rejectedTable = $('#rejectedTable').DataTable(rejectedConfig);
        }
        
        // Recalculate DataTables columns when tab is shown
        if (target === '#pending') {
            if (pendingTableShift1Table) pendingTableShift1Table.columns.adjust().responsive.recalc();
            if (pendingTableShift2Table) pendingTableShift2Table.columns.adjust().responsive.recalc();
            if (pendingTableShift3Table) pendingTableShift3Table.columns.adjust().responsive.recalc();
        } else if (target === '#approved') {
            if (approvedTableShift1Table) approvedTableShift1Table.columns.adjust().responsive.recalc();
            if (approvedTableShift2Table) approvedTableShift2Table.columns.adjust().responsive.recalc();
            if (approvedTableShift3Table) approvedTableShift3Table.columns.adjust().responsive.recalc();
        } else if (target === '#rejected' && rejectedTable) {
            rejectedTable.columns.adjust().responsive.recalc();
        }

    });

    // Event listeners for modal buttons
    $(document).on('click', '#btnSaveChecks', function() {
        const btn = $(this);
        const plotingId = btn.data('ploting-id');
        saveChecks(plotingId).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Verifikasi berhasil disimpan',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    $(document).on('click', '#btnApproveModal', function() {
        const btn = $(this);
        const id = btn.data('id');
        const plotingId = btn.data('ploting-id');
        
        // Save checks first, then approve
        saveChecks(plotingId).then(() => {
            $('#detailModal').modal('hide');
            approveInput(id);
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'Gagal menyimpan verifikasi', 'error');
        });
    });

    $(document).on('click', '#btnRejectModal', function() {
        const id = $(this).data('id');
        $('#detailModal').modal('hide');
        showRejectModal(id);
    });

    // Date filter event listeners
    $('#btnApplyDateFilter').on('click', function() {
        currentStartDate = $('#start_date').val();
        currentEndDate = $('#end_date').val();
        
        // Show loading state
        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        loadAllData();
    });

    $('#btnResetDateFilter').on('click', function() {
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        $('#end_date').val(today);
        currentStartDate = today;
        currentEndDate = today;
        
        // Show loading state
        Swal.fire({
            title: 'Mereset data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        loadAllData();
    });
});


function loadAllData() {
    $.ajax({
        url: '/api/approval/data',
        method: 'GET',
        data: {
            start_date: currentStartDate,
            end_date: currentEndDate
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function(response) {
            if (response.success) {
                pendingData = response.data.pending || [];
                approvedData = response.data.approved || [];
                rejectedData = response.data.rejected || [];

                applyBagianFilter();
                
                // Ensure DataTable is initialized for the active tab after a short delay
                setTimeout(function() {
                    if ($('#pending').hasClass('active')) {
                        // Initialize shift tables if not already initialized
                        if (!pendingTableShift1Table) {
                            const config1 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                                        $('#pendingTableShift1 tbody').html(emptyRow);
                                    }
                                }
                            };
                            pendingTableShift1Table = $('#pendingTableShift1').DataTable(config1);
                        }
                        if (!pendingTableShift2Table) {
                            const config2 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                                        $('#pendingTableShift2 tbody').html(emptyRow);
                                    }
                                }
                            };
                            pendingTableShift2Table = $('#pendingTableShift2').DataTable(config2);
                        }
                        if (!pendingTableShift3Table) {
                            const config3 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                        const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                            '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                            '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                                        $('#pendingTableShift3 tbody').html(emptyRow);
                                    }
                                }
                            };
                            pendingTableShift3Table = $('#pendingTableShift3').DataTable(config3);
                        }
                    } else if ($('#approved').hasClass('active')) {
                        // Initialize shift tables if not already initialized
                        if (!approvedTableShift1Table) {
                            const config1 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                                        const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                            '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                            '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                                        $('#approvedTableShift1 tbody').html(emptyRow);
                                    }
                                }
                            };
                            approvedTableShift1Table = $('#approvedTableShift1').DataTable(config1);
                        }
                        if (!approvedTableShift2Table) {
                            const config2 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                                        const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                            '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                            '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                                        $('#approvedTableShift2 tbody').html(emptyRow);
                                    }
                                }
                            };
                            approvedTableShift2Table = $('#approvedTableShift2').DataTable(config2);
                        }
                        if (!approvedTableShift3Table) {
                            const config3 = {
                                ...dataTableConfig,
                                drawCallback: function(settings) {
                                    if (this.api().data().length === 0) {
                                        const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                                            '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                                            '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                                        $('#approvedTableShift3 tbody').html(emptyRow);
                                    }
                                }
                            };
                            approvedTableShift3Table = $('#approvedTableShift3').DataTable(config3);
                        }
                    } else if ($('#rejected').hasClass('active') && !rejectedTable) {
                        const rejectedConfig = {
                            ...dataTableConfig,
                            drawCallback: function(settings) {
                                if (this.api().data().length === 0) {
                                    const emptyRow = '<tr><td colspan="15" class="text-center text-muted py-4">' +
                                        '<i class="fas fa-times-circle fa-3x mb-3 d-block"></i>' +
                                        '<h5>Tidak ada data rejected</h5></td></tr>';
                                    $('#rejectedTable tbody').html(emptyRow);
                                }
                            }
                        };
                        rejectedTable = $('#rejectedTable').DataTable(rejectedConfig);
                    }
                }, 100);

                // Close loading swal if open
                if (Swal.isVisible()) {
                    Swal.close();
                }
            } else {

                console.error('API returned success=false:', response);
                Swal.fire('Error', response.message || 'Gagal memuat data', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error loading data:', xhr);
            let errorMessage = 'Gagal memuat data. Silakan refresh halaman.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melihat data ini.';
            } else if (xhr.status === 500) {
                errorMessage = 'Terjadi kesalahan server. Silakan hubungi administrator.';
            }
            
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

function applyBagianFilter() {
    visiblePendingData = getBagianFilteredData(pendingData);
    pendingByShift = groupDataByShift(visiblePendingData);

    visibleApprovedData = getBagianFilteredData(approvedData);
    approvedByShift = groupDataByShift(visibleApprovedData);

    visibleRejectedData = getBagianFilteredData(rejectedData);

    updateCounts();
    renderPendingTable();
    renderApprovedTable();
    renderRejectedTable();
}

function getBagianFilteredData(source = []) {
    if (currentBagianFilter === 'all') {
        return [...source];
    }

    return source.filter(item => resolveBagianValue(item) === currentBagianFilter);
}

function resolveBagianValue(item) {
    if (!item) {
        return 'inject';
    }

    const raw = (item.bagian || '').toLowerCase();
    if (raw === 'assy' || raw === 'inject') {
        return raw;
    }

    if (item.meja_name) {
        return 'assy';
    }

    return 'inject';
}

function groupDataByShift(data = []) {
    const grouped = {
        shift_1: [],
        shift_2: [],
        shift_3: []
    };

    data.forEach(function(item) {
        if (item.shift === 'pagi' || (item.shift_display && item.shift_display.includes('Shift 1'))) {
            grouped.shift_1.push(item);
        } else if (item.shift === 'siang' || (item.shift_display && item.shift_display.includes('Shift 2'))) {
            grouped.shift_2.push(item);
        } else if (item.shift === 'malam' || (item.shift_display && item.shift_display.includes('Shift 3'))) {
            grouped.shift_3.push(item);
        }
    });

    return grouped;
}

function setBagianFilter(bagian = 'all') {
    currentBagianFilter = bagian || 'all';
    applyBagianFilter();
}

window.setBagianFilter = setBagianFilter;

function updateCounts() {
    $('#pendingCount').text(visiblePendingData.length);
    $('#approvedCount').text(visibleApprovedData.length);
    $('#rejectedCount').text(visibleRejectedData.length);
    
    // Update pending shift counts
    $('#pendingShift1Count').text(pendingByShift.shift_1.length);
    $('#pendingShift2Count').text(pendingByShift.shift_2.length);
    $('#pendingShift3Count').text(pendingByShift.shift_3.length);
    
    // Update approved shift counts
    $('#approvedShift1Count').text(approvedByShift.shift_1.length);
    $('#approvedShift2Count').text(approvedByShift.shift_2.length);
    $('#approvedShift3Count').text(approvedByShift.shift_3.length);
}

function renderPendingTable() {
    // Render each shift table
    renderPendingTableByShift('shift_1', 'pendingTableShift1');
    renderPendingTableByShift('shift_2', 'pendingTableShift2');
    renderPendingTableByShift('shift_3', 'pendingTableShift3');
}

function renderPendingTableByShift(shiftKey, tableId) {
    // Destroy existing DataTable if it exists
    let existingTable = null;
    if (tableId === 'pendingTableShift1' && pendingTableShift1Table) {
        existingTable = pendingTableShift1Table;
        pendingTableShift1Table.destroy();
        pendingTableShift1Table = null;
    } else if (tableId === 'pendingTableShift2' && pendingTableShift2Table) {
        existingTable = pendingTableShift2Table;
        pendingTableShift2Table.destroy();
        pendingTableShift2Table = null;
    } else if (tableId === 'pendingTableShift3' && pendingTableShift3Table) {
        existingTable = pendingTableShift3Table;
        pendingTableShift3Table.destroy();
        pendingTableShift3Table = null;
    }
    
    const shiftData = pendingByShift[shiftKey] || [];
    let html = '';
    
    if (shiftData.length === 0) {
        html = '';
    } else {
        shiftData.forEach((item, index) => {
            const total = item.ok_count + item.ng_count;
            const efficiency = total > 0 ? ((item.ok_count / total) * 100).toFixed(1) : 100;
            const effClass = efficiency >= 90 ? 'efficiency-high' : (efficiency >= 70 ? 'efficiency-medium' : 'efficiency-low');
            
            const bagianValue = resolveBagianValue(item);
            const bagianBadge = bagianValue === 'assy' 
                ? '<span class="badge badge-info">Assy</span>' 
                : '<span class="badge badge-primary">Inject</span>';
            const mesinOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mesin_name || '-');
            const moldOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mold_no || '-');
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(item.created_at)}</td>
                    <td data-bagian="${bagianValue}">${bagianBadge}</td>
                    <td>${item.operator_name || '-'}</td>
                    <td><span class="badge badge-secondary">${item.grup_name || '-'}</span></td>
                    <td>
                        <strong>${item.part_code}</strong><br>
                        <small class="text-muted">${item.part_name}</small>
                    </td>
                    <td>${moldOrMeja}</td>
                    <td>${mesinOrMeja}</td>
                    <td><span class="badge badge-success">${item.ok_count}</span></td>
                    <td><span class="badge badge-danger">${item.ng_count}</span></td>
                    <td><span class="badge badge-primary">${total}</span></td>
                    <td>${item.target}</td>
                    <td><span class="${effClass}">${efficiency}%</span></td>
                    <td class="text-center">
                        ${item.is_verified 
                            ? '<span class="badge badge-success mb-1 d-block"><i class="fas fa-check-circle"></i> Terverifikasi</span>' 
                            : '<span class="badge badge-secondary mb-1 d-block"><i class="fas fa-clock"></i> Belum Verifikasi</span>'}
                        <div class="btn-action-group">
                            <button class="btn btn-warning btn-sm btn-action" onclick="showPersiapanDetail(${item.id})" title="Verifikasi Persiapan">
                                <i class="fas fa-clipboard-check"></i>
                            </button>
                            <button class="btn btn-info btn-sm btn-action" onclick="showDetail(${item.id})" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-success btn-sm btn-action" onclick="approveInput(${item.id}, ${item.is_verified})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-action" onclick="showRejectModal(${item.id})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#' + tableId + ' tbody').html(html);
    
    // Initialize DataTable only if tab is visible
    if ($('#pending').hasClass('active')) {
        const pendingConfig = {
            ...dataTableConfig,
            drawCallback: function(settings) {
                // Show empty state if no data
                if (this.api().data().length === 0) {
                    const emptyRow = '<tr><td colspan="14" class="text-center text-muted py-4">' +
                        '<i class="fas fa-clock fa-3x mb-3 d-block"></i>' +
                        '<h5>Tidak ada data pending untuk shift ini</h5></td></tr>';
                    $('#' + tableId + ' tbody').html(emptyRow);
                }
            }
        };
        
        if (tableId === 'pendingTableShift1') {
            pendingTableShift1Table = $('#' + tableId).DataTable(pendingConfig);
        } else if (tableId === 'pendingTableShift2') {
            pendingTableShift2Table = $('#' + tableId).DataTable(pendingConfig);
        } else if (tableId === 'pendingTableShift3') {
            pendingTableShift3Table = $('#' + tableId).DataTable(pendingConfig);
        }
    }
}

function renderApprovedTable() {
    // Render each shift table
    renderApprovedTableByShift('shift_1', 'approvedTableShift1');
    renderApprovedTableByShift('shift_2', 'approvedTableShift2');
    renderApprovedTableByShift('shift_3', 'approvedTableShift3');
}

function renderApprovedTableByShift(shiftKey, tableId) {
    // Destroy existing DataTable if it exists
    let existingTable = null;
    if (tableId === 'approvedTableShift1' && approvedTableShift1Table) {
        existingTable = approvedTableShift1Table;
        approvedTableShift1Table.destroy();
        approvedTableShift1Table = null;
    } else if (tableId === 'approvedTableShift2' && approvedTableShift2Table) {
        existingTable = approvedTableShift2Table;
        approvedTableShift2Table.destroy();
        approvedTableShift2Table = null;
    } else if (tableId === 'approvedTableShift3' && approvedTableShift3Table) {
        existingTable = approvedTableShift3Table;
        approvedTableShift3Table.destroy();
        approvedTableShift3Table = null;
    }
    
    const shiftData = approvedByShift[shiftKey] || [];
    let html = '';
    
    if (shiftData.length === 0) {
        html = '';
    } else {
        shiftData.forEach((item, index) => {
            const total = item.ok_count + item.ng_count;
            const efficiency = total > 0 ? ((item.ok_count / total) * 100).toFixed(1) : 100;
            const effClass = efficiency >= 90 ? 'efficiency-high' : (efficiency >= 70 ? 'efficiency-medium' : 'efficiency-low');
            
            const bagianValue = resolveBagianValue(item);
            const bagianBadge = bagianValue === 'assy' 
                ? '<span class="badge badge-info">Assy</span>' 
                : '<span class="badge badge-primary">Inject</span>';
            const mesinOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mesin_name || '-');
            const moldOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mold_no || '-');
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(item.created_at)}</td>
                    <td data-bagian="${bagianValue}">${bagianBadge}</td>
                    <td>${item.operator_name || '-'}</td>
                    <td><span class="badge badge-secondary">${item.grup_name || '-'}</span></td>
                    <td>
                        <strong>${item.part_code}</strong><br>
                        <small class="text-muted">${item.part_name}</small>
                    </td>
                    <td>${moldOrMeja}</td>
                    <td>${mesinOrMeja}</td>
                    <td><span class="badge badge-success">${item.ok_count}</span></td>
                    <td><span class="badge badge-danger">${item.ng_count}</span></td>
                    <td><span class="badge badge-primary">${total}</span></td>
                    <td>${item.target}</td>
                    <td><span class="${effClass}">${efficiency}%</span></td>
                    <td>${item.approved_by_name || '-'}</td>
                    <td>${formatDateTime(item.approved_at)}</td>
                    <td class="text-center">
                        <div class="btn-action-group">
                            <button class="btn btn-warning btn-sm btn-action" onclick="showPersiapanDetail(${item.id})" title="Verifikasi Persiapan">
                                <i class="fas fa-clipboard-check"></i>
                            </button>
                            <button class="btn btn-info btn-sm btn-action" onclick="showDetail(${item.id})" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#' + tableId + ' tbody').html(html);
    
    // Initialize DataTable only if tab is visible
    if ($('#approved').hasClass('active')) {
        const approvedConfig = {
            ...dataTableConfig,
            drawCallback: function(settings) {
                // Show empty state if no data
                if (this.api().data().length === 0) {
                    const emptyRow = '<tr><td colspan="16" class="text-center text-muted py-4">' +
                        '<i class="fas fa-check-circle fa-3x mb-3 d-block"></i>' +
                        '<h5>Tidak ada data approved untuk shift ini</h5></td></tr>';
                    $('#' + tableId + ' tbody').html(emptyRow);
                }
            }
        };
        
        if (tableId === 'approvedTableShift1') {
            approvedTableShift1Table = $('#' + tableId).DataTable(approvedConfig);
        } else if (tableId === 'approvedTableShift2') {
            approvedTableShift2Table = $('#' + tableId).DataTable(approvedConfig);
        } else if (tableId === 'approvedTableShift3') {
            approvedTableShift3Table = $('#' + tableId).DataTable(approvedConfig);
        }
    }
}

function renderRejectedTable() {
    // Destroy existing DataTable if it exists
    if (rejectedTable) {
        rejectedTable.destroy();
        rejectedTable = null;
    }
    
    let html = '';
    
    if (visibleRejectedData.length === 0) {
        // Don't add empty row here, let DataTables handle it via drawCallback
        html = '';
    } else {
        visibleRejectedData.forEach((item, index) => {
            const total = item.ok_count + item.ng_count;
            
            const bagianValue = resolveBagianValue(item);
            const bagianBadge = bagianValue === 'assy' 
                ? '<span class="badge badge-info">Assy</span>' 
                : '<span class="badge badge-primary">Inject</span>';
            const mesinOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mesin_name || '-');
            const moldOrMeja = bagianValue === 'assy' ? (item.meja_name || '-') : (item.mold_no || '-');
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(item.created_at)}</td>
                    <td data-bagian="${bagianValue}">${bagianBadge}</td>
                    <td>${item.operator_name || '-'}</td>
                    <td><span class="badge badge-secondary">${item.grup_name || '-'}</span></td>
                    <td>
                        <strong>${item.part_code}</strong><br>
                        <small class="text-muted">${item.part_name}</small>
                    </td>
                    <td>${moldOrMeja}</td>
                    <td>${mesinOrMeja}</td>
                    <td><span class="badge badge-success">${item.ok_count}</span></td>
                    <td><span class="badge badge-danger">${item.ng_count}</span></td>
                    <td><span class="badge badge-primary">${total}</span></td>
                    <td><small>${item.rejection_reason || '-'}</small></td>
                    <td>${item.rejected_by_name || '-'}</td>
                    <td>${formatDateTime(item.rejected_at)}</td>
                    <td class="text-center">
                        <div class="btn-action-group">
                            <button class="btn btn-warning btn-sm btn-action" onclick="showPersiapanDetail(${item.id})" title="Verifikasi Persiapan">
                                <i class="fas fa-clipboard-check"></i>
                            </button>
                            <button class="btn btn-info btn-sm btn-action" onclick="showDetail(${item.id})" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#rejectedTable tbody').html(html);
    
    // Initialize DataTable only if table is visible
    if ($('#rejected').hasClass('active')) {
        const rejectedConfig = {
            ...dataTableConfig,
            drawCallback: function(settings) {
                // Show empty state if no data
                if (this.api().data().length === 0) {
                                    const emptyRow = '<tr><td colspan="15" class="text-center text-muted py-4">' +
                                        '<i class="fas fa-times-circle fa-3x mb-3 d-block"></i>' +
                                        '<h5>Tidak ada data rejected</h5></td></tr>';
                    $('#rejectedTable tbody').html(emptyRow);
                }
            }
        };
        rejectedTable = $('#rejectedTable').DataTable(rejectedConfig);
    }
}

function approveInput(id, isVerified) {
    if (!isVerified) {
        Swal.fire({
            title: 'Belum Verifikasi!',
            text: 'Anda harus melakukan verifikasi persiapan proses produksi terlebih dahulu (klik tombol kuning).',
            icon: 'warning',
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Saya Mengerti'
        });
        return;
    }

    Swal.fire({
        title: 'Approve Produksi?',
        text: 'Data akan disetujui dan tidak dapat diubah',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Approve',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `/input-produksi/approve/${id}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                        loadAllData();
                    } else {
                        Swal.fire('Error', response.message || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'Anda tidak memiliki akses untuk melakukan approve';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Terjadi kesalahan server. Silakan hubungi administrator.';
                    }
                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        }
    });
}

function showRejectModal(id) {
    $('#rejectInputId').val(id);
    $('#rejectionReason').val('');
    $('#rejectModal').modal('show');
}

function confirmReject() {
    const id = $('#rejectInputId').val();
    const reason = $('#rejectionReason').val().trim();
    
    if (!reason) {
        Swal.fire('Peringatan', 'Alasan penolakan harus diisi', 'warning');
        return;
    }
    
    // Show loading
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/input-produksi/reject/${id}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { reason: reason },
        success: function(response) {
            if (response.success) {
                $('#rejectModal').modal('hide');
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                loadAllData();
            } else {
                Swal.fire('Error', response.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melakukan reject';
            } else if (xhr.status === 500) {
                errorMessage = 'Terjadi kesalahan server. Silakan hubungi administrator.';
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

function showDetail(id) {
    // Reset modal content
    $('#detailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat data detail...</p>
        </div>
    `);
    
    // Show modal
    $('#detailModal').modal('show');
    
    // Load detail data
    $.ajax({
        url: `/api/approval/detail/${id}`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                renderDetailContent(response.data);
            } else {
                $('#detailContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> ${response.message || 'Gagal memuat data detail'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Gagal memuat data detail';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#detailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                </div>
            `);
        }
    });
}

function renderDetailContent(data) {
    const total = (data.ok_count || 0) + (data.ng_count || 0);
    const efficiency = total > 0 ? ((data.ok_count / total) * 100).toFixed(1) : 100;
    const effClass = efficiency >= 90 ? 'efficiency-high' : (efficiency >= 70 ? 'efficiency-medium' : 'efficiency-low');
    const gap = Math.max(0, (data.target || 0) - total);
    
    // 1. Informasi Umum & Part & Mesin
    let html = `
        <div class="row">
            <!-- Informasi Umum -->
            <div class="col-md-6">
                <div class="detail-section">
                    <div class="detail-section-title">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Umum
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Input</div>
                        <div class="detail-value">${formatDateTime(data.created_at)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Operator</div>
                        <div class="detail-value">${data.operator_name || '-'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="badge badge-${data.status === 'pending' ? 'warning' : (data.status === 'approved' ? 'success' : 'danger')}">
                                ${data.status === 'pending' ? 'Pending' : (data.status === 'approved' ? 'Approved' : 'Rejected')}
                            </span>
                        </div>
                    </div>
                    ${data.submitted_at ? `
                    <div class="detail-item">
                        <div class="detail-label">Waktu Submit</div>
                        <div class="detail-value">${formatDateTime(data.submitted_at)}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <!-- Informasi Part & Mesin -->
            <div class="col-md-6">
                <div class="detail-section">
                    <div class="detail-section-title">
                        <i class="fas fa-cog mr-2"></i>Informasi Part & Mesin
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Part Code</div>
                        <div class="detail-value"><strong>${data.part_code || '-'}</strong></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Part Name</div>
                        <div class="detail-value">${data.part_name || '-'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mold No</div>
                        <div class="detail-value">${data.mold_no || '-'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mesin</div>
                        <div class="detail-value">${data.mesin_name || '-'}</div>
                    </div>
                    ${data.customer ? `
                    <div class="detail-item">
                        <div class="detail-label">Customer</div>
                        <div class="detail-value">${data.customer}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
        
        <!-- Statistik Produksi -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-chart-bar mr-2"></i>Statistik Produksi
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="detail-item text-center">
                        <div class="detail-label">Target</div>
                        <div class="detail-value">
                            <span class="badge badge-primary" style="font-size: 1.1rem; padding: 0.5em 0.8em;">
                                ${data.target || 0}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="detail-item text-center">
                        <div class="detail-label">Good (OK)</div>
                        <div class="detail-value">
                            <span class="badge badge-success" style="font-size: 1.1rem; padding: 0.5em 0.8em;">
                                ${data.ok_count || 0}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="detail-item text-center">
                        <div class="detail-label">Defect (NG)</div>
                        <div class="detail-value">
                            <span class="badge badge-danger" style="font-size: 1.1rem; padding: 0.5em 0.8em;">
                                ${data.ng_count || 0}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="detail-item text-center">
                        <div class="detail-label">Total Produksi</div>
                        <div class="detail-value">
                            <span class="badge badge-info" style="font-size: 1.1rem; padding: 0.5em 0.8em;">
                                ${total}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="detail-item">
                        <div class="detail-label">Gap</div>
                        <div class="detail-value">
                            <span class="badge badge-${gap > 0 ? 'warning' : 'success'}" style="font-size: 1rem;">
                                ${gap}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <div class="detail-label">Efficiency</div>
                        <div class="detail-value">
                            <span class="${effClass}" style="font-size: 1.2rem; font-weight: bold;">
                                ${efficiency}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // NG Details by Category
    if (data.ng_material || data.ng_process || data.ng_machine || data.ng_operator || data.ng_other) {
        html += `
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Detail NG per Kategori
                </div>
                <div class="row">
                    ${data.ng_material ? `
                    <div class="col-md-4 mb-3">
                        <div class="detail-item">
                            <div class="detail-label">NG Material</div>
                            <div class="detail-value">
                                <span class="badge badge-danger">${data.ng_material}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${data.ng_process ? `
                    <div class="col-md-4 mb-3">
                        <div class="detail-item">
                            <div class="detail-label">NG Process</div>
                            <div class="detail-value">
                                <span class="badge badge-danger">${data.ng_process}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${data.ng_machine ? `
                    <div class="col-md-4 mb-3">
                        <div class="detail-item">
                            <div class="detail-label">NG Machine</div>
                            <div class="detail-value">
                                <span class="badge badge-danger">${data.ng_machine}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${data.ng_operator ? `
                    <div class="col-md-4 mb-3">
                        <div class="detail-item">
                            <div class="detail-label">NG Operator</div>
                            <div class="detail-value">
                                <span class="badge badge-danger">${data.ng_operator}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${data.ng_other ? `
                    <div class="col-md-4 mb-3">
                        <div class="detail-item">
                            <div class="detail-label">NG Other</div>
                            <div class="detail-value">
                                <span class="badge badge-danger">${data.ng_other}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    // Approval/Rejection Info
    if (data.status === 'approved' && data.approved_by_name) {
        html += `
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-check-circle mr-2 text-success"></i>Informasi Approval
                </div>
                <div class="detail-item">
                    <div class="detail-label">Approved By</div>
                    <div class="detail-value">${data.approved_by_name}</div>
                </div>
                ${data.approved_at ? `
                <div class="detail-item">
                    <div class="detail-label">Approved At</div>
                    <div class="detail-value">${formatDateTime(data.approved_at)}</div>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    if (data.status === 'rejected' && data.rejected_by_name) {
        html += `
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-times-circle mr-2 text-danger"></i>Informasi Rejection
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rejected By</div>
                    <div class="detail-value">${data.rejected_by_name}</div>
                </div>
                ${data.rejected_at ? `
                <div class="detail-item">
                    <div class="detail-label">Rejected At</div>
                    <div class="detail-value">${formatDateTime(data.rejected_at)}</div>
                </div>
                ` : ''}
                ${data.rejection_reason ? `
                <div class="detail-item">
                    <div class="detail-label">Alasan Penolakan</div>
                    <div class="detail-value">
                        <div class="alert alert-danger mb-0">${data.rejection_reason}</div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    $('#detailContent').html(html);

    // Update buttons visibility
    if (data.status === 'pending') {
        $('#btnRejectModal').removeClass('d-none').data('id', data.id);
        $('#btnApproveModal').removeClass('d-none').data('id', data.id).data('ploting-id', data.ploting_id);
    } else {
        $('#btnRejectModal').addClass('d-none');
        $('#btnApproveModal').addClass('d-none');
    }
}

function showPersiapanDetail(id) {
    $('#persiapanDetailModal').modal('show');
    $('#persiapanDetailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted font-weight-medium">Memuat data persiapan...</p>
        </div>
    `);

    $.get(`/api/approval/detail/${id}`, function(response) {
        if (response.success) {
            renderPersiapanContent(response.data);
        } else {
            $('#persiapanDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>Gagal memuat data: ${response.message}
                </div>
            `);
        }
    }).fail(function(xhr) {
        $('#persiapanDetailContent').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i>Terjadi kesalahan sistem.
            </div>
        `);
    });
}

function renderPersiapanContent(data) {
    const checks = data.persiapan_checks || [];
    const isPending = data.status === 'pending';
    const shift = data.shift || '';
    const shiftSuffix = shift ? ' (' + shift.charAt(0).toUpperCase() + shift.slice(1) + ')' : '';

    let html = `
        <div class="alert alert-warning mb-4">
            <div class="row">
                <div class="col-md-6">
                    <strong>Operator:</strong> ${data.operator_name || '-'}<br>
                    <strong>Shift:</strong> ${shift.toUpperCase() || '-'}
                </div>
                <div class="col-md-6">
                    <strong>Part:</strong> ${data.part_name}<br>
                    <strong>Mesin/Meja:</strong> ${data.mesin_name || data.meja_name || '-'}
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40%">Item Pengecekan</th>
                        <th class="text-center" style="width: 15%">Operator</th>
                        <th class="text-center" style="width: 15%">Leader</th>
                        <th style="width: 30%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
    `;

    checklistItems.forEach((item, index) => {
        const check = checks.find(c => c.item_name === item + shiftSuffix) || 
                      checks.find(c => c.item_name === item) || {};
        
        const isOpChecked = check.operator_checked ? 'checked' : '';
        const isLeaderChecked = check.leader_checked ? 'checked' : '';
        const keterangan = check.keterangan || '';
        const disabled = !isPending ? 'disabled' : '';

        html += `
            <tr>
                <td class="align-middle">${index + 1}. ${item}</td>
                <td class="text-center align-middle">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input op-check" 
                            id="modal_op_check_${index}" ${isOpChecked} disabled>
                        <label class="custom-control-label" for="modal_op_check_${index}"></label>
                    </div>
                </td>
                <td class="text-center align-middle">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input leader-check" 
                            id="modal_leader_check_${index}" 
                            data-item="${item}" 
                            data-shift-suffix="${shiftSuffix}"
                            ${isLeaderChecked} ${disabled}>
                        <label class="custom-control-label" for="modal_leader_check_${index}"></label>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm leader-note" 
                        value="${keterangan}" 
                        data-item="${item}"
                        ${disabled} placeholder="Keterangan">
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#persiapanDetailContent').html(html);
    
    if (isPending) {
        $('#btnSavePersiapanModal').show().off('click').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            
            saveChecks(data.ploting_id).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Verifikasi persiapan berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#persiapanDetailModal').modal('hide');
                loadAllData(); // Reload data to update status badges
            }).catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: typeof err === 'string' ? err : 'Terjadi kesalahan saat menyimpan'
                });
            }).finally(() => {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Verifikasi');
            });
        });
    } else {
        $('#btnSavePersiapanModal').hide();
    }
}

function saveChecks(plotingId) {
    return new Promise((resolve, reject) => {
        const items = [];
        // Use the context of the modal or the specific table
        $('#persiapanDetailContent .leader-check').each(function(index) {
            const item = $(this).data('item');
            const shiftSuffix = $(this).data('shift-suffix') || '';
            const leaderChecked = $(this).is(':checked');
            const operatorChecked = $(`#modal_op_check_${index}`).is(':checked');
            const keterangan = $(`#persiapanDetailContent .leader-note[data-item="${item}"]`).val();
            
            items.push({
                item_name: item + shiftSuffix,
                operator_checked: operatorChecked ? 1 : 0,
                leader_checked: leaderChecked ? 1 : 0,
                keterangan: keterangan
            });
        });

        $.ajax({
            url: `/input-produksi/persiapan/${plotingId}`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { items: items },
            success: function(response) {
                if (response.success) {
                    resolve(response);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr) {
                reject(xhr);
            }
        });
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

