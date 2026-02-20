<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="modalEditLabel">
                        <i class="fas fa-edit"></i> Edit Data BOM
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Mold --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Mold</label>
                                <select name="moldshop_id" id="edit_moldshop_id" class="form-control" required>
                                    <option value="">-- Pilih Mold --</option>
                                    @foreach ($moldshops as $mold)
                                        <option value="{{ $mold->id }}">{{ $mold->no_mold }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Part --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Part</label>
                                <select name="part_id" id="edit_part_id" class="form-control" required>
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
                                <input type="number" name="cavity_mold" id="edit_cavity_mold"
                                    class="form-control" min="1" required>
                            </div>
                        </div>

                        {{-- Cycle Time Std --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Cycle Time Std (detik)</label>
                                <input type="number" step="0.01" name="cycle_time_std" id="edit_cycle_time_std"
                                    class="form-control" required>
                            </div>
                        </div>

                        {{-- Resin Type --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Resin Type</label>
                                <input type="text" name="resin_type" id="edit_resin_type" class="form-control">
                            </div>
                        </div>

                        {{-- Shot Weight --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Shot Weight (gram)</label>
                                <input type="number" step="0.01" name="shot_weight" id="edit_shot_weight" class="form-control">
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Remarks (Keterangan)</label>
                                <textarea name="remarks" id="edit_remarks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
