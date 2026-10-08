<!-- Modal Edit Jadwal Pemeliharaan (Tailwind CSS) -->
<div class="modal fade" id="modalEditJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-600 via-indigo-700 to-indigo-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold tracking-tight text-white mb-0 leading-tight">
                            Update Jadwal Pemeliharaan
                        </h5>
                        <p class="text-[11px] text-indigo-100 font-medium mb-0">
                            Bulan <span id="labelBulan" class="font-bold underline"></span> &bull; Minggu ke-<span id="labelMinggu" class="font-bold underline"></span>
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer border-0" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 space-y-4">
                {{-- Info Mesin Card --}}
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/90 dark:border-slate-700/60 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Unit Mesin</span>
                        <span id="labelMesin" class="text-sm font-bold text-slate-900 dark:text-white truncate block"></span>
                    </div>
                </div>

                {{-- Form --}}
                <form id="formUpdateJadwal" action="{{ route('jadwal-pemeliharaan.update-cell') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="mesin_id" id="inputMesinId">
                    <input type="hidden" name="tahun" id="inputTahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" id="inputBulan">
                    <input type="hidden" name="minggu" id="inputMinggu">
                    <input type="hidden" id="inputJadwalId">
                    
                    {{-- Section Plan --}}
                    <div id="sectionPlan" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 mb-0">
                                <i class="fas fa-bullseye"></i>
                                <span>Target Perencanaan (PLAN)</span>
                            </label>
                            <span class="text-[10px] text-slate-400 font-medium">S / M / O</span>
                        </div>
                        @if($canEditPlan)
                        <div class="relative">
                            <input type="text" 
                                name="plan_tipe" 
                                id="inputPlan" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-bold uppercase placeholder:normal-case placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" 
                                placeholder="Ketik S, M, O atau Tanggal..." 
                                list="planOptions" 
                                autocomplete="off">
                            <datalist id="planOptions">
                                <option value="S">S - Small Maintenance (Mingguan/Bulanan)</option>
                                <option value="M">M - Medium Maintenance (Perawatan Menengah)</option>
                                <option value="O">O - Overhaul Maintenance (Perawatan Besar)</option>
                            </datalist>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">S: Small</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60">M: Medium</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">O: Overhaul</span>
                        </div>
                        @else
                        <input type="text" id="inputPlanDisplay" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold" readonly placeholder="Tidak Ada Akses Edit Plan">
                        <input type="hidden" name="plan_tipe" id="inputPlan">
                        @endif
                    </div>

                    {{-- Section Actual --}}
                    <div id="sectionActual" class="space-y-3">
                        <div class="p-4 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 mb-0">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Pelaksanaan Lapangan (ACTUAL)</span>
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">R&M Realisasi</span>
                            </div>

                            @if($canEditActual)
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 block mb-1">Tanggal Realisasi Pelaksanaan</label>
                                <input type="text" 
                                    name="aktual_tanggal" 
                                    id="inputActual" 
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700/60 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-bold placeholder:font-normal placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none" 
                                    placeholder="Contoh: 12/01 atau 2026-01-12" 
                                    autocomplete="off">
                            </div>

                            <div>
                                <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-camera text-slate-400"></i>
                                    <span>Upload Bukti Foto / Dokumen (Wajib)</span>
                                </label>
                                <input type="file" 
                                    name="bukti" 
                                    id="inputBukti" 
                                    class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 dark:file:bg-emerald-950 dark:file:text-emerald-300 hover:file:bg-emerald-200 transition-all cursor-pointer" 
                                    accept="image/*,application/pdf">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">Format: JPG, PNG, PDF. Ukuran Maksimum: 5MB</span>
                            </div>

                            <div id="previewBukti" style="display:none;" class="pt-1">
                                <a href="#" id="linkBukti" target="_blank" class="inline-flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-100 transition-all">
                                    <i class="fas fa-external-link-alt text-[11px]"></i>
                                    <span>Lihat Bukti Terupload</span>
                                </a>
                            </div>
                            @else
                            <div>
                                <input type="text" id="inputActualDisplay" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold" readonly placeholder="Tidak Ada Akses Edit Actual">
                                <input type="hidden" name="aktual_tanggal" id="inputActual">
                            </div>
                            <div id="previewBuktiReadOnly" style="display:none;" class="pt-1">
                                <a href="#" id="linkBuktiReadOnly" target="_blank" class="inline-flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-100 transition-all">
                                    <i class="fas fa-eye text-[11px]"></i>
                                    <span>Lihat Bukti Lapangan</span>
                                </a>
                            </div>
                            @endif
                        </div>

                        {{-- Status & Approval (Leader R&M / Kabag / Super Admin) --}}
                        <div id="approvalSection" style="display:none;" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 space-y-2.5">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Status Otorisasi:</span>
                                    <span id="statusLabel" class="badge px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white shadow-sm"></span>
                                </div>
                                <div id="approvalButtons" style="display:none;" class="flex items-center gap-1.5">
                                    @if(in_array(Auth::user()->role, ['super_admin', 'kabag', 'manager_plant']) || (Auth::user()->role === 'leader' && Auth::user()->departemen === 'Repair & Maintenance'))
                                    <button type="button" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition-all cursor-pointer border-0" onclick="approveJadwal()">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                    <button type="button" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-sm transition-all cursor-pointer border-0" onclick="rejectJadwal()">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div id="approvedBySection" style="display:none;" class="text-[11px] text-slate-500 dark:text-slate-400">
                                <i class="fas fa-user-check text-emerald-500 mr-1"></i>
                                Disetujui oleh: <strong id="labelApprovedBy" class="text-slate-700 dark:text-slate-200"></strong> pada <span id="labelApprovedAt"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-1.5">
                            <i class="fas fa-comment-alt text-slate-400"></i>
                            <span>Catatan / Keterangan</span>
                        </label>
                        <textarea name="keterangan" 
                            id="inputKeterangan" 
                            rows="2" 
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" 
                            placeholder="Catatan pengerjaan, kendala spare part, atau catatan teknisi..."></textarea>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200/90 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer border-0" data-dismiss="modal" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer border-0" id="btnSaveJadwal" onclick="saveJadwal()">
                    <i class="fas fa-save"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </div>
    </div>
</div>
