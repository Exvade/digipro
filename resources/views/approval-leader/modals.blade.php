<!-- Modal Reject Reason -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>Reject Produksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rejectInputId">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Peringatan:</strong> Data yang ditolak tidak akan masuk ke sistem OEE.
                </div>
                <div class="form-group">
                    <label for="rejectionReason" class="font-weight-bold">
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="rejectionReason"
                        rows="5"
                        placeholder="Masukkan alasan penolakan secara detail..."
                        required
                        style="resize: vertical; min-height: 100px;"></textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Alasan penolakan wajib diisi untuk keperluan dokumentasi dan perbaikan.
                    </small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="fas fa-ban mr-1"></i>Reject Produksi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>Detail Produksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="detailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted font-weight-medium">Memuat data detail...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary d-none" id="btnSaveChecks">
                    <i class="fas fa-save mr-1"></i>Simpan Verifikasi
                </button>
                <button type="button" class="btn btn-danger d-none" id="btnRejectModal">
                    <i class="fas fa-times mr-1"></i>Reject
                </button>
                <button type="button" class="btn btn-success d-none" id="btnApproveModal">
                    <i class="fas fa-check mr-1"></i>Approve & Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Persiapan Detail -->
<div class="modal fade" id="persiapanDetailModal" tabindex="-1" role="dialog" aria-labelledby="persiapanDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="persiapanDetailModalLabel">
                    <i class="fas fa-clipboard-check mr-2"></i>Verifikasi Persiapan Produksi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="persiapanDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted font-weight-medium">Memuat data persiapan...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary" id="btnSavePersiapanModal">
                    <i class="fas fa-save mr-1"></i>Simpan Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>