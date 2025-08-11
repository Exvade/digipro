<form id="editMesinForm" method="POST" action="{{ route('mesin.update', $mesin->id) }}">
    @csrf
    @method('PUT')
    
    <div class="form-group">
        <label for="nama_mesin">Nama Mesin <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nama_mesin" name="nama_mesin" value="{{ $mesin->nama_mesin }}" required>
    </div>
    
    <div class="form-group">
        <label for="brand_mesin">Brand Mesin <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="brand_mesin" name="brand_mesin" value="{{ $mesin->brand_mesin }}" required>
    </div>
    
    <div class="form-group">
        <label for="tonase_mesin">Tonase Mesin (Ton) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="tonase_mesin" name="tonase_mesin" value="{{ $mesin->tonase_mesin }}" step="0.01" min="0" required>
    </div>
    
    <div class="form-group">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select class="form-control" id="status" name="status" required>
            <option value="aktif" {{ $mesin->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="maintenance" {{ $mesin->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="tidak aktif" {{ $mesin->status == 'tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#editMesinForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Terjadi kesalahan!';
                
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });
});
</script> 