$(document).ready(function() {
    // Initialize DataTable with basic configuration
    $('#mesinTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']]
    });
});

// Function to open create modal
function openCreateModal() {
    $('#addModal').modal('show');
    
    $.ajax({
        url: window.createModalUrl,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#addModalBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat form...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#addModalBody').html(response);
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat form.';
            
            if (xhr.status === 404) {
                errorMessage = 'Route tidak ditemukan.';
            } else if (xhr.status === 401) {
                errorMessage = 'Anda harus login terlebih dahulu.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses ke halaman ini.';
            }
            
            $('#addModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to open edit modal
function openEditModal(mesinId) {
    $('#editModal').modal('show');
    
    $.ajax({
        url: '/mesin/' + mesinId + '/edit',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#editModalBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#editModalBody').html(response);
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat data.';
            
            if (xhr.status === 404) {
                errorMessage = 'Data mesin tidak ditemukan.';
            } else if (xhr.status === 401) {
                errorMessage = 'Anda harus login terlebih dahulu.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses ke data ini.';
            }
            
            $('#editModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to delete mesin
function deleteMesin(mesinId, mesinName) {
    if (confirm(`Apakah Anda yakin ingin menghapus mesin "${mesinName}"?`)) {
        $.ajax({
            url: '/mesin/' + mesinId,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    alert('Data mesin berhasil dihapus.');
                    location.reload();
                } else {
                    alert('Terjadi kesalahan: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                
                if (xhr.status === 404) {
                    errorMessage = 'Data mesin tidak ditemukan.';
                } else if (xhr.status === 401) {
                    errorMessage = 'Anda harus login terlebih dahulu.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Anda tidak memiliki akses untuk menghapus data ini.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                alert(errorMessage);
            }
        });
    }
}

// Handle form submissions
$(document).on('submit', '#addForm', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#addModal').modal('hide');
                alert('Data mesin berhasil ditambahkan.');
                location.reload();
            } else {
                alert('Terjadi kesalahan: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            alert(errorMessage);
        },
        complete: function() {
            submitBtn.html('<i class="fas fa-save mr-1"></i>Simpan');
            submitBtn.prop('disabled', false);
        }
    });
});

$(document).on('submit', '#editMesinForm', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                alert('Data mesin berhasil diperbarui.');
                location.reload();
            } else {
                alert('Terjadi kesalahan: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            alert(errorMessage);
        },
        complete: function() {
            submitBtn.html('Simpan Perubahan');
            submitBtn.prop('disabled', false);
        }
    });
}); 