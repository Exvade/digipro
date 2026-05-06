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
                <form id="formUpdateJadwal" action="{{ route('jadwal-pemeliharaan.update-cell') }}">
                    @csrf
                    <input type="hidden" name="mesin_id" id="inputMesinId">
                    <input type="hidden" name="tahun" id="inputTahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" id="inputBulan">
                    <input type="hidden" name="minggu" id="inputMinggu">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3" id="sectionPlan">
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

                        <div class="col-md-6 mb-3" id="sectionActual">
                            <label class="form-label fw-bold text-success">Pelaksanaan (ACTUAL)</label>
                            @if($canEditActual)
                            <input type="text" name="aktual_tanggal" id="inputActual" class="form-control border-success shadow-sm" placeholder="Cth: 12/01" autocomplete="off">
                            @else
                            <input type="text" id="inputActualDisplay" class="form-control" readonly placeholder="Tidak Ada Akses">
                            <input type="hidden" name="aktual_tanggal" id="inputActual">
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4" onclick="saveJadwal(urlUpdateJadwal)"><i class="fas fa-save"></i> Simpan Data</button>
            </div>
        </div>
    </div>
</div>
