// Parts Index Page JavaScript - Simplified

$(document).ready(function() {
    // Initialize SweetAlert
    if (typeof Swal === 'undefined') {
        // Load SweetAlert if not already loaded
        $('head').append('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>');
    }

    // Initialize DataTable
    if ($.fn.dataTable.isDataTable('#partsTable')) {
        $('#partsTable').DataTable().destroy();
    }
    
    $('#partsTable').DataTable({
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        order: [[0, 'asc']],
        dom: 'frtip',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            emptyTable: "Tidak ada data parts",
            zeroRecords: "Tidak ada data yang sesuai dengan filter",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });


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
    // Search functionality
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
                    <td colspan="8" class="text-center text-muted empty-state">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <br>
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            `;
        } else {
            parts.forEach(function(part, index) {
                tbody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <span class="font-weight-bold text-primary">${part.no_parts}</span>
                        </td>
                        <td>
                            <span class="text-muted">${part.no_barang || '-'}</span>
                        </td>
                        <td>
                            <div class="text-truncate-2" title="${part.nama_part}">
                                ${part.nama_part}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-secondary">${part.type}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">${parseInt(part.quantity).toLocaleString()}</span>
                        </td>
                        <td>
                            <span class="badge badge-success">${parseFloat(part.bruto).toLocaleString()}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-info btn-sm" onclick="viewPart(${part.id})" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="editPart(${part.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deletePart(${part.id}, '${part.nama_part}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
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
        
        // Show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        
        // Get form data
        const formData = new FormData(this);
        
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
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan');
            }
        });
    });

    // Edit Part Form Handler
    $('#editPartForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#editSubmitBtn');
        
        // Show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...');
        
        // Get form data
        const formData = new FormData(this);
        
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
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Update');
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

    // Auto-generate part number (optional)
    $('#type').on('change', function() {
        const type = $(this).val();
        if (type && !$('#no_parts').val()) {
            // Generate part number based on type
            const timestamp = Date.now().toString().slice(-6);
            $('#no_parts').val(`${type.toUpperCase()}-${timestamp}`);
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
            $('#view_no_parts').text(response.no_parts);
            $('#view_no_barang').text(response.no_barang || '-');
            $('#view_type').text(response.type);
            $('#view_nama_part').text(response.nama_part);
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
    // Fetch part data first
    $.ajax({
        url: `/parts/${partId}/edit`,
        method: 'GET',
        success: function(response) {
            // Populate form fields
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
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
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
                        text: 'Data part berhasil dihapus.',
                        timer: 2000,
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
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

// Show notification function (fallback)
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
        // Fallback to alert if SweetAlert is not available
        alert(message);
    }
}
