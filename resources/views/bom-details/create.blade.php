<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <form action="{{ route('bom-details.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCreateLabel">
                        <i class="fas fa-layer-group"></i> Tambah Data BOM
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Mold --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Mold</label>
                                <select id="moldSelect" name="moldshop_id" class="form-control" required>
                                    <option value="">-- Pilih Mold --</option>
                                    @foreach ($moldshops as $mold)
                                        <option value="{{ $mold->id }}" data-nama="{{ $mold->nama_mold }}">
                                            {{ $mold->no_mold }} - {{ $mold->nama_mold }}
                                        </option>
                                    @endforeach
                                </select>
                                <small id="moldNameDisplay" class="text-muted d-none">
                                    <i class="fas fa-info-circle"></i> Nama Mold: <span id="moldNameText"></span>
                                </small>
                            </div>
                        </div>

                        {{-- Part --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Part</label>
                                <select name="part_id" class="form-control" required>
                                    <option value="">-- Pilih Part --</option>
                                    @foreach ($parts as $part)
                                        <option value="{{ $part->id }}">{{ $part->nama_part }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Cavity Mold --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Cavity Mold</label>
                                <input type="number" name="cavity_mold" class="form-control" min="1" required>
                            </div>
                        </div>

                        {{-- Cycle Time Standard --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Cycle Time Std (detik)</label>
                                <input type="number" step="0.01" name="cycle_time_std" class="form-control" required>
                            </div>
                        </div>

                        {{-- Resin Type --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Resin Type</label>
                                <input type="text" name="resin_type" class="form-control"
                                    placeholder="Contoh: ABS, PP, PC" required>
                            </div>
                        </div>

                        {{-- Shot Weight --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Shot Weight (gram)</label>
                                <input type="number" step="0.01" name="shot_weight" class="form-control"
                                    placeholder="Contoh: 150.5" required>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Remarks (Keterangan)</label>
                                <textarea name="remarks" class="form-control" rows="2"
                                    placeholder="Keterangan tambahan jika ada..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

