<form id="editForm" action="{{ route('mesin.update', $mesin->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="form-group">
        <label for="nama_mesin" class="form-label">
            Nama Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" 
               name="nama_mesin" 
               id="nama_mesin" 
               value="{{ old('nama_mesin', $mesin->nama_mesin) }}"
               class="form-control"
               placeholder="Masukkan nama mesin"
               required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="brand_mesin" class="form-label">
            Brand Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" 
               name="brand_mesin" 
               id="brand_mesin" 
               value="{{ old('brand_mesin', $mesin->brand_mesin) }}"
               class="form-control"
               placeholder="Masukkan brand mesin"
               required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="tonase_mesin" class="form-label">
            Tonase Mesin <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input type="number" 
                   name="tonase_mesin" 
                   id="tonase_mesin" 
                   value="{{ old('tonase_mesin', $mesin->tonase_mesin) }}"
                   class="form-control"
                   placeholder="Masukkan tonase mesin"
                   step="0.01"
                   min="0"
                   max="999999"
                   required>
            <div class="input-group-append">
                <span class="input-group-text">Ton</span>
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="status" class="form-label">
            Status <span class="text-danger">*</span>
        </label>
        <select name="status" 
                id="status" 
                class="form-control"
                required>
            <option value="">Pilih status</option>
            <option value="aktif" {{ old('status', $mesin->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="maintenance" {{ old('status', $mesin->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="tidak aktif" {{ old('status', $mesin->status) == 'tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" 
                class="btn btn-secondary" 
                data-dismiss="modal">
            <i class="fas fa-times me-2"></i>Batal
        </button>
        <button type="submit" 
                class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update
        </button>
    </div>
</form> 