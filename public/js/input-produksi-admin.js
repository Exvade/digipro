$(document).ready(function() {
    // Helper function to get route URL
    function getRoute(name, params = {}) {
        if (window.routes && window.routes[name]) {
            let url = window.routes[name];
            Object.keys(params).forEach(key => {
                url = url.replace(':' + key, params[key]);
            });
            return url;
        }
        return '#';
    }

    // Get bagian parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const bagian = urlParams.get('bagian');

    // Define columns based on bagian
    let columns = [
        { data: 'no', name: 'no', orderable: false, searchable: false },
        { data: 'bagian', name: 'bagian', className: 'text-center' },
        { data: 'tanggal', name: 'tanggal' },
        { data: 'waktu_input', name: 'waktu_input', orderable: true, searchable: false, className: 'text-center' },
        { data: 'part_code', name: 'part_code' },
        { data: 'part_name', name: 'part_name' }
    ];

    // Add mesin/meja columns based on bagian
    if (bagian === 'assy') {
        // Only show Meja for assy
        columns.push({ data: 'mesin', name: 'mesin' }); // Will show meja name
    } else if (bagian === 'inject') {
        // Show Mesin and Mold for inject
        columns.push({ data: 'mesin', name: 'mesin' });
        columns.push({ data: 'mold', name: 'mold' });
    } else {
        // Show both for "All"
        columns.push({ data: 'mesin', name: 'mesin' });
        columns.push({ data: 'mold', name: 'mold' });
    }

    // Add remaining columns
    columns = columns.concat([
        { data: 'target', name: 'target', className: 'text-right' },
        { data: 'ok', name: 'ok', className: 'text-right' },
        { data: 'ng', name: 'ng', className: 'text-right' },
        { data: 'total', name: 'total', className: 'text-right' },
        { data: 'gap', name: 'gap', className: 'text-right' },
        { data: 'progress', name: 'progress', orderable: false, searchable: false },
        { data: 'status', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
    ]);

    // Initialize DataTables
    const table = $('#inputProduksiTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: getRoute('input-produksi.datatables-data'),
            type: 'GET',
            data: function(d) {
                if (bagian) {
                    d.bagian = bagian;
                }
            },
            dataSrc: 'data'
        },
        columns: columns,
        order: [[2, 'desc']], // Order by tanggal descending (column index 2)
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            loadingRecords: "Memuat data...",
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data dalam tabel",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            },
            buttons: {
                copy: "Salin",
                csv: "CSV",
                excel: "Excel",
                pdf: "PDF",
                print: "Cetak"
            }
        },
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Salin',
                className: 'btn btn-sm btn-secondary mr-2'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-secondary mr-2'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success mr-2'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger mr-2',
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Cetak',
                className: 'btn btn-sm btn-info'
            }
        ],
        drawCallback: function(settings) {
            // Re-initialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // View detail handler
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        const modal = $('#detailModal');
        const modalBody = $('#detailModalBody');
        
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
            url: getRoute('input-produksi.show', { id: id }),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    
                    // Status badge
                    let statusBadge = '';
                    if (data.status === 'approved') {
                        statusBadge = '<span class="badge badge-success badge-lg"><i class="fas fa-check-circle mr-1"></i>Approved</span>';
                    } else if (data.status === 'rejected') {
                        statusBadge = '<span class="badge badge-danger badge-lg"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
                    } else if (data.status === 'pending') {
                        statusBadge = '<span class="badge badge-warning badge-lg"><i class="fas fa-clock mr-1"></i>Pending</span>';
                    } else {
                        statusBadge = '<span class="badge badge-secondary badge-lg"><i class="fas fa-spinner mr-1"></i>In Progress</span>';
                    }
                    
                    // Build HTML content
                    let html = `
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Tanggal Input</div>
                                                <div>${data.created_at}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Status</div>
                                                <div>${statusBadge}</div>
                                            </div>
                                            ${data.submitted_at ? `<div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Tanggal Submit</div>
                                                <div>${data.submitted_at}</div>
                                            </div>` : ''}
                                            ${data.approved_at ? `<div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Tanggal Approval</div>
                                                <div>${data.approved_at}</div>
                                            </div>` : ''}
                                            ${data.rejected_at ? `<div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Tanggal Rejection</div>
                                                <div>${data.rejected_at}</div>
                                            </div>` : ''}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-clipboard-list mr-2"></i>Informasi Planning Produksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-tag text-primary mr-1"></i>Part Code</div>
                                                <div class="font-weight-bold">${data.part_code}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-box text-primary mr-1"></i>Part Name</div>
                                                <div>${data.part_name}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-cogs text-primary mr-1"></i>Machine</div>
                                                <div>${data.mesin}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-toolbox text-primary mr-1"></i>Mold Name</div>
                                                <div>${data.mold_name}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-building text-primary mr-1"></i>Customer</div>
                                                <div>${data.customer}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small"><i class="fas fa-bullseye text-primary mr-1"></i>Target Produksi</div>
                                                <div class="font-weight-bold text-primary">${parseInt(data.target).toLocaleString('id-ID')}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-user mr-2"></i>Informasi Operator</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">Operator</div>
                                                <div>${data.operator_name}</div>
                                            </div>
                                            ${data.approved_by ? `<div class="col-md-6 mb-3">
                                                <div class="font-weight-bold text-muted small">${data.status === 'approved' ? 'Approved By' : (data.status === 'rejected' ? 'Rejected By' : '')}</div>
                                                <div>${data.approved_by}</div>
                                            </div>` : ''}
                                        </div>
                                    </div>
                                </div>
                                
                                ${data.status === 'rejected' && data.ng_description ? `
                                <div class="card mb-3 border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Alasan Rejection</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">${data.ng_description}</p>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                            
                            <div class="col-lg-4 mb-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-chart-bar mr-2"></i>Statistik Produksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3 p-3 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <div style="font-size: 2rem; font-weight: bold;">${parseInt(data.target).toLocaleString('id-ID')}</div>
                                            <div class="small">Target</div>
                                        </div>
                                        <div class="text-center mb-3 p-3 rounded" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                                            <div style="font-size: 2rem; font-weight: bold;">${parseInt(data.ok_count).toLocaleString('id-ID')}</div>
                                            <div class="small">Good Product</div>
                                        </div>
                                        <div class="text-center mb-3 p-3 rounded" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white;">
                                            <div style="font-size: 2rem; font-weight: bold;">${parseInt(data.ng_count).toLocaleString('id-ID')}</div>
                                            <div class="small">Defect Product</div>
                                        </div>
                                        <div class="text-center mb-3 p-3 rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                                            <div style="font-size: 2rem; font-weight: bold;">${parseInt(data.gap).toLocaleString('id-ID')}</div>
                                            <div class="small">Gap</div>
                                        </div>
                                    </div>
                                </div>
                                
                                ${data.ng_count > 0 ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-circle mr-2"></i>Detail NG</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Material</div>
                                            <div>${parseInt(data.ng_material).toLocaleString('id-ID')}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Process</div>
                                            <div>${parseInt(data.ng_process).toLocaleString('id-ID')}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Machine</div>
                                            <div>${parseInt(data.ng_machine).toLocaleString('id-ID')}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Operator</div>
                                            <div>${parseInt(data.ng_operator).toLocaleString('id-ID')}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Other</div>
                                            <div>${parseInt(data.ng_other).toLocaleString('id-ID')}</div>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                                
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-tasks mr-2"></i>Progress</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="font-weight-bold">Progress Produksi</span>
                                                <span class="font-weight-bold">${data.percentage}%</span>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated ${data.percentage >= 100 ? 'bg-success' : (data.percentage >= 70 ? 'bg-info' : 'bg-warning')}" 
                                                     role="progressbar" 
                                                     style="width: ${data.percentage}%"
                                                     aria-valuenow="${data.percentage}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    ${data.percentage}%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Total Produksi</div>
                                            <div class="font-weight-bold">${parseInt(data.total).toLocaleString('id-ID')}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="font-weight-bold text-muted small">Efficiency</div>
                                            <div class="font-weight-bold">${data.efficiency}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    modalBody.html(html);
                } else {
                    modalBody.html('<div class="alert alert-danger">Gagal memuat data detail</div>');
                }
            },
            error: function(xhr) {
                modalBody.html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data: ' + (xhr.responseJSON?.message || 'Unknown error') + '</div>');
            }
        });
    });

    // Approve handler
    $(document).on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        const button = $(this);

        Swal.fire({
            title: 'Approve Data?',
            text: 'Apakah Anda yakin ingin menyetujui data input produksi ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Approve',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: getRoute('input-produksi.approve', { id: id }),
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil di-approve',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Gagal approve data'
                            });
                            button.prop('disabled', false).html('<i class="fas fa-check"></i>');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat approve data'
                        });
                        button.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                });
            }
        });
    });

    // Reject handler
    $(document).on('click', '.btn-reject', function() {
        const id = $(this).data('id');
        const button = $(this);

        Swal.fire({
            title: 'Reject Data?',
            text: 'Apakah Anda yakin ingin menolak data input produksi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reject',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: getRoute('input-produksi.reject', { id: id }),
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil di-reject',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Gagal reject data'
                            });
                            button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat reject data'
                        });
                        button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                    }
                });
            }
        });
    });
});

