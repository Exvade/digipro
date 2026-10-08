<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="modalShowLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalShowLabel">
                            Detail Planning Produksi
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Rincian data teknis alokasi, target shift, dan distribusi kuantitas per jam
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-6 max-h-[calc(85vh-130px)] overflow-y-auto custom-scrollbar" id="modalShowContent">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- Left Column: Info Utama & Target (5 cols) --}}
                    <div class="lg:col-span-5 space-y-5">
                        {{-- 1. General Info Card --}}
                        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle text-indigo-500"></i> Informasi Umum
                                </span>
                                <span id="show-bagian-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">-</span>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40" id="row-no_mold" style="display:none;">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">No Mold:</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200" id="show-no_mold">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40" id="row-mesin" style="display:none;">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Mesin:</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" id="show-mesin">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40" id="row-meja" style="display:none;">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Meja:</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" id="show-meja">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Tanggal:</span>
                                    <span class="font-mono text-slate-800 dark:text-slate-200" id="show-tanggal">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Lot Produksi:</span>
                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" id="show-lot">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">No Part:</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200" id="show-no_part">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">No Barang:</span>
                                    <span class="font-mono text-slate-600 dark:text-slate-300" id="show-no_barang">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Nama Part:</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-right max-w-[200px] truncate" id="show-nama_part">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Tipe Part:</span>
                                    <span class="text-slate-700 dark:text-slate-300" id="show-type">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Qty Planning:</span>
                                    <span class="font-mono font-black text-sm text-indigo-600 dark:text-indigo-400" id="show-qty">-</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/40 dark:border-slate-700/40">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Status Approval:</span>
                                    <span id="show-status-approval">-</span>
                                </div>
                                <div class="flex items-start justify-between py-1">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Catatan:</span>
                                    <span class="text-slate-700 dark:text-slate-300 text-right max-w-[220px]" id="show-catatan">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Target Per Shift Card --}}
                        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-3">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                <i class="fas fa-bullseye text-amber-500"></i>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Target Per Shift</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Shift 1 (Pagi)</span>
                                    <span class="font-mono font-black text-base text-slate-900 dark:text-white" id="show-target_shift_pagi">0</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-600 block">Shift 2 (Siang)</span>
                                    <span class="font-mono font-black text-base text-slate-900 dark:text-white" id="show-target_shift_siang">0</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 block">Shift 3 (Malam)</span>
                                    <span class="font-mono font-black text-base text-slate-900 dark:text-white" id="show-target_shift_malam">0</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300 block">Total Shift</span>
                                    <span class="font-mono font-black text-base text-emerald-700 dark:text-emerald-300" id="show-total_shift">0</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Operator List (jika ada) --}}
                        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-2">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                <i class="fas fa-users text-indigo-500"></i>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Operator Bertugas</span>
                            </div>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="text-[10px] uppercase font-bold text-slate-400">
                                            <th class="py-1">Operator</th>
                                            <th class="py-1">Grup</th>
                                            <th class="py-1">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="show-operators-body" class="divide-y divide-slate-200/40 dark:divide-slate-700/40">
                                        {{-- Populated by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Detail Jam Per Shift (7 cols) --}}
                    <div class="lg:col-span-7 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="fas fa-clock text-indigo-500"></i> Rencana Distribusi Jam Kerja
                            </span>
                        </div>

                        {{-- Shift 1 (Pagi) --}}
                        <div class="border border-amber-200/80 dark:border-amber-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                            <div class="px-4 py-2 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200/60 dark:border-amber-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                    <i class="fas fa-sun text-amber-500"></i>
                                    <span>SHIFT 1 (07:00 - 15:00)</span>
                                </span>
                                <span class="text-xs font-mono font-bold text-amber-900 dark:text-amber-200">
                                    Total: <span id="total-pagi">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <table class="w-full text-center border-collapse">
                                    <thead>
                                        <tr class="text-[10px] font-bold text-slate-400">
                                            @for ($i = 7; $i <= 14; $i++)
                                            <th class="p-1 border border-slate-200/50 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 font-mono">{{ sprintf('%02d:00', $i) }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="shift-pagi-row" class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                                            @for ($i = 7; $i <= 14; $i++)
                                            <td id="pagi_jam_{{ sprintf('%02d', $i) }}" class="p-2 border border-slate-200/50 dark:border-slate-800 bg-white dark:bg-slate-900">0</td>
                                            @endfor
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Shift 2 (Siang) --}}
                        <div class="border border-sky-200/80 dark:border-sky-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                            <div class="px-4 py-2 bg-sky-50 dark:bg-sky-950/40 border-b border-sky-200/60 dark:border-sky-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-sky-800 dark:text-sky-300 flex items-center gap-1.5">
                                    <i class="fas fa-cloud-sun text-sky-500"></i>
                                    <span>SHIFT 2 (15:00 - 23:00)</span>
                                </span>
                                <span class="text-xs font-mono font-bold text-sky-900 dark:text-sky-200">
                                    Total: <span id="total-siang">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <table class="w-full text-center border-collapse">
                                    <thead>
                                        <tr class="text-[10px] font-bold text-slate-400">
                                            @for ($i = 15; $i <= 22; $i++)
                                            <th class="p-1 border border-slate-200/50 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 font-mono">{{ sprintf('%02d:00', $i) }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="shift-siang-row" class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                                            @for ($i = 15; $i <= 22; $i++)
                                            <td id="siang_jam_{{ sprintf('%02d', $i) }}" class="p-2 border border-slate-200/50 dark:border-slate-800 bg-white dark:bg-slate-900">0</td>
                                            @endfor
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Shift 3 (Malam) --}}
                        <div class="border border-indigo-200/80 dark:border-indigo-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                            <div class="px-4 py-2 bg-indigo-50 dark:bg-indigo-950/40 border-b border-indigo-200/60 dark:border-indigo-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-1.5">
                                    <i class="fas fa-moon text-indigo-500"></i>
                                    <span>SHIFT 3 (23:00 - 07:00)</span>
                                </span>
                                <span class="text-xs font-mono font-bold text-indigo-900 dark:text-indigo-200">
                                    Total: <span id="total-malam">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <table class="w-full text-center border-collapse">
                                    <thead>
                                        <tr class="text-[10px] font-bold text-slate-400">
                                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                            <th class="p-1 border border-slate-200/50 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 font-mono">{{ sprintf('%02d:00', $i) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="shift-malam-row" class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                            <td id="malam_jam_{{ sprintf('%02d', $i) }}" class="p-2 border border-slate-200/50 dark:border-slate-800 bg-white dark:bg-slate-900">0</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Grand Total Card --}}
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-blue-600 text-white shadow-lg shadow-indigo-500/25 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-100 block">Total Akumulasi Produksi</span>
                                    <span class="text-xs text-white/80">Semua Shift (1, 2, 3)</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black font-mono tracking-tight" id="show-grandtotal">0</span>
                                <span class="text-xs text-white/80 block font-medium">Pcs</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-750 transition-all cursor-pointer" data-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>