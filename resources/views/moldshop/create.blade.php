<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel" aria-hidden="true" data-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded">
            <form action="{{ route('moldshop.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCreateLabel">
                        <i class="fas fa-plus-circle"></i> Tambah Mold
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>No Mold</label>
                            <input type="text" name="no_mold" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama Mold</label>
                            <input type="text" name="nama_mold" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Type</label>
                            <input type="text" name="type" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Customer</label>
                            <input type="text" name="customer" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                          <label>Status</label>
                          <select name="status" class="form-control" required>
                              <option value="OK" selected>OK</option>
                              <option value="REPAIR">REPAIR</option>
                              <option value="PREVENTIVE">PREVENTIVE</option>
                          </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
  </div>
  