// Parts Index Page JavaScript - Modernized for Tailwind & DataTables

$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.dataTable.isDataTable('#partsTable')) {
        $('#partsTable').DataTable().destroy();
    }
    
    const partsTable = $('#partsTable').DataTable({
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        language: {
            "emptyTable": "Tidak ada data part yang tersedia",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ part",
            "infoEmpty": "Menampilkan 0 part",
            "infoFiltered": "(disaring dari _MAX_ total part)",
            "lengthMenu": "Tampilkan _MENU_ part",
            "loadingRecords": "Memuat data...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "searchPlaceholder": "Cari no part, nama, tipe...",
            "zeroRecords": "Tidak ditemukan part yang sesuai",
            "paginate": {
                "first": '<i class="fas fa-angle-double-left text-xs"></i>',
                "last": '<i class="fas fa-angle-double-right text-xs"></i>',
                "next": '<i class="fas fa-angle-right text-xs"></i>',
                "previous": '<i class="fas fa-angle-left text-xs"></i>'
            }
        },
        dom: "<'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4'<'w-full sm:w-auto'l><'w-full sm:w-auto'f>>" +
             "<'overflow-x-auto custom-scrollbar'tr>" +
             "<'flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800'<'text-xs font-medium text-slate-500 dark:text-slate-400'i><'flex items-center gap-1'p>>",
        columnDefs: [
            {
                targets: -1, // Aksi column
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                targets: [0, 4, 5, 6], // No, Type, Quantity, Bruto
                className: 'text-center'
            }
        ]
    });

    // ==========================================================================
    // KPI Cards: Counter Animation & Interactive Table Filtering
    // ==========================================================================
    initKpiCounters();
    initKpiCardFilters();

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var isDecimal = $this.data('decimal') === true || $this.data('decimal') === 'true';
            var target = parseFloat($this.data('target')) || 0;
            if (target === 0) {
                $this.text(isDecimal ? '0.00' : '0');
                return;
            }

            var duration = 750;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = start + (target - start) * ease;

                if (isDecimal) {
                    $this.text(currentVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                } else {
                    $this.text(Math.floor(currentVal).toLocaleString());
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    if (isDecimal) {
                        $this.text(target.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    } else {
                        $this.text(target.toLocaleString());
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiCardFilters() {
        $('.kpi-card').on('click', function() {
            var $card = $(this);
            var filter = $card.data('filter');
            var isAlreadyActive = $card.hasClass('active');

            // Reset active states
            $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            if (isAlreadyActive && filter !== 'all') {
                // Deselect: return to default sorting
                $('.kpi-card[data-filter="all"]').addClass('active ring-2 ring-indigo-500/20');
                $('.kpi-card[data-filter="all"] .kpi-active-indicator').removeClass('hidden');
                partsTable.order([[0, 'asc']]).draw();
                return;
            }

            $card.addClass('active ring-2 ring-indigo-500/20');
            $card.find('.kpi-active-indicator').removeClass('hidden');

            if (filter === 'all') {
                partsTable.order([[0, 'asc']]).search('').draw();
            } else if (filter === 'quantity') {
                // Sort by Quantity column (index 5) descending
                partsTable.order([[5, 'desc']]).draw();
            } else if (filter === 'bruto') {
                // Sort by Bruto column (index 6) descending
                partsTable.order([[6, 'desc']]).draw();
            } else if (filter === 'type') {
                // Sort by Type column (index 4) ascending
                partsTable.order([[4, 'asc']]).draw();
            }
        });
    }

    // Event listeners for action buttons using event delegation
    $(document).on('click', '.btn-view-part', function() {
        const partId = $(this).data('part-id');
        viewPart(partId);
    });

    $(document).on('click', '.btn-edit-part', function() {
        const partId = $(this).data('part-id');
        editPart(partId);
    });

    $(document).on('click', '.btn-delete-part', function() {
        const partId = $(this).data('part-id');
        const partName = $(this).data('part-name');
        deletePart(partId, partName);
    });

    // Optional Search functionality (if custom search triggers exist)
    $('#searchBtn').click(function() {
        performSearch();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            performSearch();
        }
    });

    function performSearch() {
        const searchTerm = $('#searchInput').val();
        
        $.ajax({
            url: '/parts/search',
            method: 'GET',
            data: { search: searchTerm },
            success: function(response) {
                updateTable(response);
            },
            error: function(xhr) {
                showNotification('Terjadi kesalahan saat mencari data', 'error');
            }
        });
    }

    function updateTable(parts) {
        let tbody = '';
        
        if (parts.length === 0) {
            tbody = `
                <tr>
                    <td colspan="8" class="text-center text-slate-400 dark:text-slate-500 py-8">
                        <i class="fas fa-search text-2xl mb-2 block"></i>
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            `;
        } else {
            parts.forEach(function(part, index) {
                tbody += `
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-400 dark:text-slate-500">${index + 1}</td>
                        <td>
                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">${part.no_parts}</span>
                        </td>
                        <td>
                            <span class="font-mono text-xs text-slate-600 dark:text-slate-400">${part.no_barang || '-'}</span>
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200" title="${part.nama_part}">
                                ${part.nama_part}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                ${part.type}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 whitespace-nowrap">
                                ${parseInt(part.quantity).toLocaleString()}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 whitespace-nowrap">
                                ${parseFloat(part.bruto).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" class="btn-view-part w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center justify-center transition-all cursor-pointer" data-part-id="${part.id}" title="Lihat Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button type="button" class="btn-edit-part w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center justify-center transition-all cursor-pointer" data-part-id="${part.id}" title="Edit Part">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button type="button" class="btn-delete-part w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800/60 inline-flex items-center justify-center transition-all cursor-pointer" data-part-id="${part.id}" data-part-name="${part.nama_part}" title="Hapus Part">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        
        $('#partsTableBody').html(tbody);
        $('#totalParts').text(parts.length);
    }

    // Create Part Form Handler
    $('#createPartForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const originalHtml = submitBtn.html();
        
        // Show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...');
        
        // Get form data
        const formData = new FormData(this);
        
        // Clear previous errors
        $('#createPartForm .is-invalid').removeClass('is-invalid');
        $('#createPartForm .invalid-feedback').remove();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Part berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`#${field}`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Edit Part Form Handler
    $('#editPartForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#editSubmitBtn');
        const originalHtml = submitBtn.html();
        
        // Show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i>Mengupdate...');
        
        // Get form data
        const formData = new FormData(this);
        
        // Clear previous errors
        $('#editPartForm .is-invalid').removeClass('is-invalid');
        $('#editPartForm .invalid-feedback').remove();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Part berhasil diperbarui',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat memperbarui data';
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`#edit_${field}`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Auto-format number inputs
    $('#quantity, #bruto, #edit_quantity, #edit_bruto').on('input', function() {
        let value = $(this).val();
        if (value < 0) {
            $(this).val(0);
        }
    });

    // Clear form when modal is closed
    $('#createPartModal').on('hidden.bs.modal', function() {
        $('#createPartForm')[0].reset();
        $('#createPartForm .is-invalid').removeClass('is-invalid');
        $('#createPartForm .invalid-feedback').remove();
    });

    $('#editPartModal').on('hidden.bs.modal', function() {
        $('#editPartForm')[0].reset();
        $('#editPartForm .is-invalid').removeClass('is-invalid');
        $('#editPartForm .invalid-feedback').remove();
    });
});

// Function to view part details
function viewPart(partId) {
    $.ajax({
        url: `/parts/${partId}`,
        method: 'GET',
        success: function(response) {
            $('#view_no_parts').text(response.no_parts || '-');
            $('#view_no_barang').text(response.no_barang || '-');
            $('#view_type').text(response.type || '-');
            $('#view_nama_part').text(response.nama_part || '-');
            $('#view_quantity').text(number_format(response.quantity));
            $('#view_bruto').text(number_format(response.bruto, 2));
            $('#viewPartModal').modal('show');
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memuat data part.'
            });
        }
    });
}

// Function to open edit modal and populate data
function editPart(partId) {
    $.ajax({
        url: `/parts/${partId}/edit`,
        method: 'GET',
        success: function(response) {
            $('#edit_part_id').val(partId);
            $('#edit_no_parts').val(response.no_parts);
            $('#edit_no_barang').val(response.no_barang);
            $('#edit_type').val(response.type);
            $('#edit_nama_part').val(response.nama_part);
            $('#edit_quantity').val(response.quantity);
            $('#edit_bruto').val(response.bruto);
            
            // Set form action
            $('#editPartForm').attr('action', `/parts/${partId}`);
            
            // Show modal
            $('#editPartModal').modal('show');
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memuat data part.'
            });
        }
    });
}

// Function to delete part
function deletePart(partId, partName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus part "${partName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'rounded-xl font-bold text-xs px-4 py-2.5',
            cancelButton: 'rounded-xl font-bold text-xs px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send delete request
            $.ajax({
                url: `/parts/${partId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Data part berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                }
            });
        }
    });
}

// Helper function for number formatting
function number_format(number, decimals = 0) {
    const num = parseFloat(number) || 0;
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(num);
}

// Show notification function
function showNotification(message, type) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Berhasil!' : 'Oops...',
            text: message,
            showConfirmButton: false,
            timer: type === 'success' ? 1500 : 3000
        });
    } else {
        alert(message);
    }
}
