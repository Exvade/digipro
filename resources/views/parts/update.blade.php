<!-- Edit Part Modal -->
<div class="modal fade" id="editPartModal" tabindex="-1" aria-labelledby="editPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPartModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Part
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPartForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_part_id" name="part_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_no_parts">
                                    No Part <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('no_parts') is-invalid @enderror" 
                                       id="edit_no_parts" name="no_parts" 
                                       placeholder="Masukkan nomor part" required>
                                @error('no_parts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_type">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                       id="edit_type" name="type" 
                                       placeholder="Masukkan type part" required>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_part">
                            Nama Part <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nama_part') is-invalid @enderror" 
                               id="edit_nama_part" name="nama_part" 
                               placeholder="Masukkan nama part lengkap" required>
                        @error('nama_part')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_quantity">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="edit_quantity" name="quantity" 
                                       min="0" placeholder="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_bruto">
                                    Bruto (kg) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('bruto') is-invalid @enderror" 
                                       id="edit_bruto" name="bruto" 
                                       min="0" step="0.01" placeholder="0.00" required>
                                @error('bruto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save mr-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
