<!-- Create Part Modal -->
<div class="modal fade" id="createPartModal" tabindex="-1" aria-labelledby="createPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPartModalLabel">
                    Tambah Part Baru
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createPartForm" action="{{ route('parts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_parts">
                                    No Part <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('no_parts') is-invalid @enderror"
                                    id="no_parts" name="no_parts" value="{{ old('no_parts') }}"
                                    placeholder="Masukkan nomor part" required>
                                @error('no_parts')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_barang">
                                    No Barang
                                </label>
                                <input type="text" class="form-control @error('no_barang') is-invalid @enderror"
                                    id="no_barang" name="no_barang" value="{{ old('no_barang') }}"
                                    placeholder="Masukkan nomor barang">
                                @error('no_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('type') is-invalid @enderror"
                                    id="type" name="type" value="{{ old('type') }}"
                                    placeholder="Masukkan type part" required>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama_part">
                            Nama Part <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nama_part') is-invalid @enderror"
                            id="nama_part" name="nama_part" value="{{ old('nama_part') }}"
                            placeholder="Masukkan nama part lengkap" required>
                        @error('nama_part')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                    id="quantity" name="quantity" value="{{ old('quantity') }}" min="0"
                                    placeholder="0" required>
                                @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bruto">
                                    Bruto (kg) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('bruto') is-invalid @enderror"
                                    id="bruto" name="bruto" value="{{ old('bruto') }}" min="0"
                                    step="0.01" placeholder="0.00" required>
                                @error('bruto')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>