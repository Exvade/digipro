<div class="modal fade" id="modalUpdate" tabindex="-1" role="dialog" aria-labelledby="modalUpdateLabel" aria-hidden="true" data-backdrop="true" data-keyboard="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <form id="updateForm" method="POST">
        @csrf @method('PUT')

        <!-- Header -->
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="modalUpdateLabel">
            <i class="fas fa-edit"></i> Update Mold
          </h5>
          <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label font-weight-bold">No Mold</label>
              <input type="text" name="no_mold" id="update_no_mold" class="form-control" placeholder="Masukkan nomor mold" required>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-bold">Nama Mold</label>
              <input type="text" name="nama_mold" id="update_nama_mold" class="form-control" placeholder="Masukkan nama mold" required>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-bold">Type</label>
              <input type="text" name="type" id="update_type" class="form-control" placeholder="Masukkan type mold" required>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-bold">Customer</label>
              <input type="text" name="customer" id="update_customer" class="form-control" placeholder="Masukkan nama customer" required>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-bold">Status</label>
              <select name="status" id="update_status" class="form-control" required>
                <option value="OK">OK</option>
                <option value="REPAIR">REPAIR</option>
                <option value="PREVENTIVE">PREVENTIVE</option>
              </select>
            </div>
            
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer d-flex justify-content-end">
          <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-warning text-dark">
            Update
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
