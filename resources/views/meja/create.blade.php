<form id="createMejaForm" method="POST" action="{{ route('meja.store') }}">
    @csrf
    <div class="form-group">
        <label for="nama_meja">Nama Meja <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('nama_meja') is-invalid @enderror" 
               id="nama_meja" 
               name="nama_meja" 
               value="{{ old('nama_meja') }}" 
               placeholder="Masukkan nama meja"
               required>
        @error('nama_meja')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select class="form-control @error('status') is-invalid @enderror" 
                id="status" 
                name="status" 
                required>
            <option value="">Pilih Status</option>
            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        @error('status')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Simpan
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Form submission handling
    $('#createMejaForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(function() {
                        // Close modal and reload page
                        $('#addModal').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    const errorMessages = [];
                    
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    
                    errorMessage = errorMessages.join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonColor: '#e74a3b'
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Clear form when modal is hidden
    $('#addModal').on('hidden.bs.modal', function() {
        $('#createMejaForm')[0].reset();
        $('#createMejaForm .is-invalid').removeClass('is-invalid');
        $('#createMejaForm .invalid-feedback').remove();
    });
});
</script>
