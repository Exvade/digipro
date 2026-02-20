<form id="createMejaForm" method="POST" action="{{ route('meja.store') }}">
    @csrf
    <div class="form-group">
        <label for="nama_meja">Nama Meja <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama_meja') is-invalid @enderror" id="nama_meja" name="nama_meja"
            value="{{ old('nama_meja') }}" placeholder="Masukkan nama meja" required>
        @error('nama_meja')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
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
            Batal
        </button>
        <button type="submit" class="btn btn-primary">
            Simpan
        </button>
    </div>
</form>