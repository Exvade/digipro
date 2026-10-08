<!-- Modal Reject Reason -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden">
            <div class="modal-header px-6 py-4 bg-gradient-to-r from-rose-500/10 via-rose-500/5 to-transparent dark:from-rose-500/20 border-b border-rose-200/50 dark:border-rose-900/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="rejectModalLabel">
                        Reject Laporan Produksi
                    </h5>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <input type="hidden" id="rejectInputId">
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/60 mb-4 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Peringatan:</strong> Data hasil produksi yang ditolak tidak akan dimasukkan ke dalam perhitungan performa OEE sistem.
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label for="rejectionReason" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all"
                        id="rejectionReason"
                        rows="4"
                        placeholder="Tuliskan alasan penolakan secara jelas dan objektif..."
                        required></textarea>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1 mt-1">
                        <i class="fas fa-info-circle text-[10px]"></i>
                        <span>Alasan penolakan tercatat untuk evaluasi dan feedback operator.</span>
                    </p>
                </div>
            </div>
            <div class="modal-footer px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                    <i class="fas fa-times text-[10px]"></i>
                    <span>Batal</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-rose-500/20 transition-all" onclick="confirmReject()">
                    <i class="fas fa-ban text-[10px]"></i>
                    <span>Konfirmasi Reject</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden">
            <div class="modal-header px-6 py-4 bg-gradient-to-r from-indigo-500/10 via-indigo-500/5 to-transparent dark:from-indigo-500/20 border-b border-indigo-200/50 dark:border-indigo-900/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="detailModalLabel">
                        Detail Hasil Produksi
                    </h5>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="detailContent">
                <div class="text-center py-10">
                    <div class="spinner-border text-indigo-500" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat rincian data...</p>
                </div>
            </div>
            <div class="modal-footer px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                    <i class="fas fa-times text-[10px]"></i>
                    <span>Tutup</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all d-none" id="btnSaveChecks">
                    <i class="fas fa-save text-[10px]"></i>
                    <span>Simpan Verifikasi</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-rose-500/20 transition-all d-none" id="btnRejectModal">
                    <i class="fas fa-times text-[10px]"></i>
                    <span>Reject</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 transition-all d-none" id="btnApproveModal">
                    <i class="fas fa-check text-[10px]"></i>
                    <span>Approve & Verifikasi</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Persiapan Detail -->
<div class="modal fade" id="persiapanDetailModal" tabindex="-1" role="dialog" aria-labelledby="persiapanDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden">
            <div class="modal-header px-6 py-4 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-500/20 border-b border-amber-200/50 dark:border-amber-900/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="persiapanDetailModalLabel">
                        Verifikasi Persiapan Kerja
                    </h5>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="persiapanDetailContent">
                <div class="text-center py-10">
                    <div class="spinner-border text-amber-500" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat checklist persiapan...</p>
                </div>
            </div>
            <div class="modal-footer px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                    <i class="fas fa-times text-[10px]"></i>
                    <span>Tutup</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all" id="btnSavePersiapanModal">
                    <i class="fas fa-save text-[10px]"></i>
                    <span>Simpan Verifikasi</span>
                </button>
            </div>
        </div>
    </div>
</div>