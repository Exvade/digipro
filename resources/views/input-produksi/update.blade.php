<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="editForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Edit Input Produksi</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <label class="form-label">Good Product (OK)</label>
                      <input type="number" class="form-control" name="ok_count" id="editOk" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Defect Product (NG)</label>
                      <input type="number" class="form-control" name="ng_count" id="editNg" required>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
              </div>
          </div>
      </form>
    </div>
  </div>
  