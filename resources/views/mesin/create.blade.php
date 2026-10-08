<!-- This form is intended to be loaded via AJAX into #addModalBody, or used as fallback -->
<form id="addForm" action="{{ route('mesin.store') }}" method="POST">
    @csrf

    <div class="space-y-4">
        {{-- Row 1: Nama Mesin & Brand --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama_mesin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Nama Mesin <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                    class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                    id="nama_mesin" name="nama_mesin" value="{{ old('nama_mesin') }}"
                    placeholder="Contoh: Mesin Inject 01" required>
            </div>

            <div>
                <label for="brand_mesin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Brand Mesin <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                    class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                    id="brand_mesin" name="brand_mesin" value="{{ old('brand_mesin') }}"
                    placeholder="Contoh: Nissei / Sumitomo / Toshiba" required>
            </div>
        </div>

        {{-- Row 2: Tonase & Status --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="tonase_mesin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Tonase Mesin <span class="text-rose-500">*</span>
                </label>
                <div class="relative flex items-center">
                    <input type="number" 
                        class="form-control w-full pl-3.5 pr-14 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                        id="tonase_mesin" name="tonase_mesin" value="{{ old('tonase_mesin') }}"
                        step="0.01" min="0" max="999999" placeholder="0.00" required>
                    <div class="absolute right-3 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-xs font-bold pointer-events-none">
                        Ton
                    </div>
                </div>
            </div>

            <div>
                <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Status Operasional <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="status" 
                    class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none cursor-pointer" required>
                    <option value="">-- Pilih Status Mesin --</option>
                    <option value="running" {{ old('status') == 'running' ? 'selected' : '' }}>Running (Beroperasi Normal)</option>
                    <option value="stop" {{ old('status') == 'stop' ? 'selected' : '' }}>Stand By (Berhenti Terencana)</option>
                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Breakdown (Perbaikan / Kerusakan)</option>
                    <option value="offline" {{ old('status') == 'offline' ? 'selected' : '' }}>Off (Tidak Ada Jadwal / Mati)</option>
                </select>
            </div>
        </div>

        {{-- Dynamic Alasan / Pesan Status --}}
        <div id="stop_reason_wrapper" class="space-y-1.5" style="display: none;">
            <label for="stop_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                Pesan / Alasan Status <span class="text-rose-500">*</span>
            </label>
            <select name="stop_reason" id="stop_reason" 
                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none cursor-pointer">
                <option value="">-- Pilih Alasan --</option>
            </select>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-0">Pilih rincian alasan terkait kondisi mesin saat ini.</p>
        </div>

        {{-- Keterangan --}}
        <div>
            <label for="keterangan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                Keterangan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea name="keterangan" id="keterangan" rows="3"
                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none resize-none"
                placeholder="Tambahkan catatan spesifikasi teknis atau catatan pemeliharaan...">{{ old('keterangan') }}</textarea>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="mt-6 pt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5">
        <button type="button" 
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" 
            data-dismiss="modal">
            Batal
        </button>
        <button type="submit" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-save text-xs"></i>
            <span>Simpan Mesin</span>
        </button>
    </div>
</form>