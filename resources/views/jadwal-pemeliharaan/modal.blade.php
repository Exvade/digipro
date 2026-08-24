<!-- Modal Edit Jadwal -->
<div class="modal fade" id="modalEditJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Update Jadwal (Bulan <span id="labelBulan"></span> / Minggu <span id="labelMinggu"></span>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-secondary mb-3 py-2">
                    <i class="fas fa-industry"></i> <strong>Mesin:</strong> <span id="labelMesin" class="fw-bold"></span>
                </div>
                <form id="formUpdateJadwal" action="{{ route('jadwal-pemeliharaan.update-cell') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="mesin_id" id="inputMesinId">
                    <input type="hidden" name="tahun" id="inputTahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" id="inputBulan">
                    <input type="hidden" name="minggu" id="inputMinggu">
                    <input type="hidden" id="inputJadwalId">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3" id="sectionPlan">
                            <label class="form-label fw-bold text-primary">Target (PLAN)</label>
                            @if($canEditPlan)
                            <input type="text" name="plan_tipe" id="inputPlan" class="form-control border-primary shadow-sm" placeholder="Isi S, M, O atau ketik Tanggal..." list="planOptions" autocomplete="off">
                            <datalist id="planOptions">
                                <option value="S">S (Small Maintenance)</option>
                                <option value="M">M (Medium Maintenance)</option>
                                <option value="O">O (Overhaul Maintenance)</option>
                            </datalist>
                            @else
                            <input type="text" id="inputPlanDisplay" class="form-control" readonly placeholder="Tidak Ada Akses">
                            <input type="hidden" name="plan_tipe" id="inputPlan">
                            @endif
                        </div>

                        <div class="col-md-12 mb-3" id="sectionActual">
                            <div class="card border-success border-2 mb-3">
                                <div class="card-body">
                                    <label class="form-label fw-bold text-success">Pelaksanaan (ACTUAL)</label>
                                    @if($canEditActual)
                                    <input type="text" name="aktual_tanggal" id="inputActual" class="form-control border-success shadow-sm mb-3" placeholder="Cth: 12/01" autocomplete="off">
                                    
                                    <label class="form-label fw-bold"><i class="fas fa-camera"></i> Upload Bukti (Wajib)</label>
                                    <input type="file" name="bukti" id="inputBukti" class="form-control mb-2" accept="image/*,application/pdf">
                                    <small class="text-muted">Format: JPG, PNG, PDF. Max: 5MB</small>
                                    <div id="previewBukti" class="mt-2 text-center" style="display:none;">
                                        <a href="#" id="linkBukti" target="_blank" class="btn btn-sm btn-info w-100"><i class="fas fa-eye"></i> Lihat Bukti Terupload</a>
                                    </div>
                                    @else
                                    <input type="text" id="inputActualDisplay" class="form-control mb-3" readonly placeholder="Tidak Ada Akses">
                                    <input type="hidden" name="aktual_tanggal" id="inputActual">
                                    <div id="previewBuktiReadOnly" class="mt-2 text-center" style="display:none;">
                                        <a href="#" id="linkBuktiReadOnly" target="_blank" class="btn btn-sm btn-info w-100"><i class="fas fa-eye"></i> Lihat Bukti</a>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Status & Approval (Leader R&M Only) -->
                            <div id="approvalSection" style="display:none;">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">Status: <span id="statusLabel" class="badge"></span></span>
                                    <div id="approvalButtons" style="display:none;">
                                        @if(in_array(Auth::user()->role, ['super_admin', 'kabag', 'manager_plant']) || (Auth::user()->role === 'leader' && Auth::user()->departemen === 'Repair & Maintenance'))
                                        <button type="button" class="btn btn-success btn-sm" onclick="approveJadwal()"><i class="fas fa-check"></i> Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="rejectJadwal()"><i class="fas fa-times"></i> Reject</button>
                                        @endif
                                    </div>
                                </div>
                                <div id="approvedBySection" style="display:none;">
                                    <small class="text-muted">Disetujui Oleh: <span id="labelApprovedBy"></span> pada <span id="labelApprovedAt"></span></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="keterangan" id="inputKeterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="btnSaveJadwal" onclick="saveJadwal()"><i class="fas fa-save"></i> Simpan Data</button>
            </div>
        </div>
    </div>
</div>
