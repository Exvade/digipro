<!-- This form is intended to be loaded via AJAX into a modal, not as a standalone page. -->

<form id="addForm" action="{{ route('mesin.store') }}" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="nama_mesin">
            Nama Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" 
               name="nama_mesin" 
               id="nama_mesin" 
               value="{{ old('nama_mesin') }}"
               class="form-control"
               placeholder="Masukkan nama mesin"
               required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="brand_mesin">
            Brand Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" 
               name="brand_mesin" 
               id="brand_mesin" 
               value="{{ old('brand_mesin') }}"
               class="form-control"
               placeholder="Masukkan brand mesin"
               required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="tonase_mesin">
            Tonase Mesin <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input type="number" 
                   name="tonase_mesin" 
                   id="tonase_mesin" 
                   value="{{ old('tonase_mesin') }}"
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
        <label for="status">
            Status <span class="text-danger">*</span>
        </label>
        <select name="status" 
                id="status" 
                class="form-control"
                required>
            <option value="">Pilih status</option>
            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="tidak aktif" {{ old('status') == 'tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>

    <div class="text-right">
        <button type="button" 
                class="btn btn-secondary mr-2" 
                data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="submit" 
                class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Simpan
        </button>
    </div>
</form> 