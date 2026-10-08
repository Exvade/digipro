<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <form action="{{ route('planning.store') }}" method="POST" id="formCreatePlanning">
                @csrf

                {{-- Header --}}
                <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalCreateLabel">
                                Tambah Planning Produksi
                            </h5>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                                Buat rencana alokasi mesin, target part, dan distribusi per jam
                            </p>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-6 space-y-6 max-h-[calc(85vh-130px)] overflow-y-auto custom-scrollbar">

                    {{-- 1. Pilihan Bagian --}}
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Pilihan Bagian</span>
                        </div>
                        <div>
                            <label for="bagian" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Bagian <span class="text-rose-500">*</span>
                            </label>
                            <select name="bagian" id="bagian" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" required>
                                <option value="inject" selected>⚙️ Inject</option>
                            </select>
                            <small class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">Pilih bagian produksi untuk alokasi mesin</small>
                        </div>
                    </div>

                    {{-- 2. Informasi Mold & Mesin (Inject) --}}
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-4" id="mold-mesin-card" style="display: none;">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Informasi Mold & Mesin Inject</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="moldshop_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    No Mold <span class="text-rose-500">*</span>
                                </label>
                                <select name="moldshop_id" id="moldshop_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                                    <option value="">-- Pilih Mold --</option>
                                    @foreach ($moldshops as $m)
                                    @php
                                    $statusUpper = strtoupper($m->status);
                                    $isDisabled = in_array($statusUpper, ['REPAIR', 'PREVENTIVE']);
                                    @endphp
                                    <option value="{{ $m->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                        {{ $m->no_mold }} - {{ $m->nama_mold }} [{{ $statusUpper }}]
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="mesin_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Mesin <span class="text-rose-500">*</span>
                                </label>
                                <select name="mesin_id" id="mesin_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                                    <option value="">-- Pilih Mesin --</option>
                                    @foreach ($mesins as $ms)
                                    @php
                                    $statusLower = strtolower($ms->status);
                                    $statusDisplay = '';
                                    if($statusLower == 'running') $statusDisplay = 'Running';
                                    elseif($statusLower == 'maintenance') $statusDisplay = 'BREAKDOWN';
                                    elseif($statusLower == 'offline') $statusDisplay = 'OFF';
                                    elseif($statusLower == 'stop') $statusDisplay = 'STAND BY';
                                    else $statusDisplay = ucfirst($statusLower);

                                    $isDisabled = in_array($statusLower, ['maintenance']);
                                    @endphp
                                    <option value="{{ $ms->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                        {{ $ms->nama_mesin }} - {{ $ms->brand_mesin }} [{{ $statusDisplay }}]
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Informasi Meja (Assembly) --}}
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-4" id="meja-card" style="display: none;">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-sky-500"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Informasi Meja Assembly</span>
                        </div>
                        <div>
                            <label for="meja_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Meja <span class="text-rose-500">*</span>
                            </label>
                            <select name="meja_id" id="meja_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                                <option value="">-- Pilih Meja --</option>
                                @foreach ($mejas as $mj)
                                @php
                                $statusMeja = strtolower($mj->status ?? 'aktif');
                                $isDisabled = in_array($statusMeja, ['tidak_aktif', 'maintenance', 'rusak']);
                                $statusDisplay = str_replace('_', ' ', ucwords(str_replace('_', ' ', $mj->status ?? 'aktif')));
                                @endphp
                                <option value="{{ $mj->id_meja }}" {{ $isDisabled ? 'disabled' : '' }}>
                                    {{ $mj->nama_meja }}
                                    @if($mj->lokasi) - {{ $mj->lokasi }} @endif
                                    [{{ $statusDisplay }}]
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 4. Parameter Produksi --}}
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Spesifikasi Target Produksi</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="tanggal" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal" id="tanggal" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" required min="{{ date('Y-m-d') }}">
                            </div>

                            <div>
                                <label for="lot_produksi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Lot Produksi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="lot_produksi" id="lot_produksi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" placeholder="Contoh: LOT-202410-01" required>
                            </div>

                            <div>
                                <label for="part_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Part Produk <span class="text-rose-500">*</span>
                                </label>
                                <select name="part_id" id="part_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" required>
                                    <option value="">-- Pilih Part --</option>
                                    @foreach ($parts as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->no_parts }} - {{ $p->nama_part }} ({{ $p->type }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                            <div>
                                <label for="qty_planning_produksi" class="block text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 mb-1.5">
                                    Qty Planning Total <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="qty_planning_produksi" id="qty_planning_produksi" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-mono font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" placeholder="0" required>
                            </div>

                            <div>
                                <label for="target_shift_pagi" class="block text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-1.5">
                                    Target Shift 1 (Pagi)
                                </label>
                                <input type="number" name="target_shift_pagi" id="target_shift_pagi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" value="0">
                            </div>

                            <div>
                                <label for="target_shift_siang" class="block text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400 mb-1.5">
                                    Target Shift 2 (Siang)
                                </label>
                                <input type="number" name="target_shift_siang" id="target_shift_siang" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" value="0">
                            </div>

                            <div>
                                <label for="target_shift_malam" class="block text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1.5">
                                    Target Shift 3 (Malam)
                                </label>
                                <input type="number" name="target_shift_malam" id="target_shift_malam" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" value="0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="total_shift" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Total Shift <small class="text-slate-400 normal-case">(Otomatis)</small>
                                </label>
                                <input type="number" name="total_shift" id="total_shift" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm font-mono font-bold" value="0" readonly>
                            </div>

                            <div class="md:col-span-3">
                                <label for="catatan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                    Catatan / Instruksi Khusus
                                </label>
                                <input type="text" name="catatan" id="catatan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" placeholder="Catatan opsional untuk tim produksi...">
                            </div>
                        </div>
                    </div>

                    {{-- 5. Rincian Distribusi Jam (Hourly Planning) --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Rencana Kuantitas Jam Kerja (Pcs/Jam)</span>
                            </div>
                        </div>

                        {{-- Shift 1 (Pagi) --}}
                        <div class="border border-amber-200/80 dark:border-amber-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                            <div class="px-4 py-2.5 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200/60 dark:border-amber-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                    <i class="fas fa-sun text-amber-500"></i>
                                    <span>SHIFT 1 (07:00 - 15:00)</span>
                                </span>
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-200 font-mono">
                                    Total Shift 1: <span id="total_pagi">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <div class="grid grid-cols-8 gap-2 min-w-[640px]">
                                    @for ($i = 7; $i <= 14; $i++)
                                    <div>
                                        <label class="block text-[10px] font-bold text-center text-slate-400 mb-1">
                                            {{ sprintf('%02d:00', $i) }}
                                        </label>
                                        <input type="number" 
                                            name="pagi_jam_{{ sprintf('%02d', $i) }}" 
                                            class="w-full text-center px-1.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all outline-none" 
                                            value="0" min="0">
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Shift 2 (Siang) --}}
                        <div class="border border-sky-200/80 dark:border-sky-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                            <div class="px-4 py-2.5 bg-sky-50 dark:bg-sky-950/40 border-b border-sky-200/60 dark:border-sky-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-sky-800 dark:text-sky-300 flex items-center gap-2">
                                    <i class="fas fa-cloud-sun text-sky-500"></i>
                                    <span>SHIFT 2 (15:00 - 23:00)</span>
                                </span>
                                <span class="text-xs font-bold text-sky-900 dark:text-sky-200 font-mono">
                                    Total Shift 2: <span id="total_siang">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <div class="grid grid-cols-8 gap-2 min-w-[640px]">
                                    @for ($i = 15; $i <= 22; $i++)
                                    <div>
                                        <label class="block text-[10px] font-bold text-center text-slate-400 mb-1">
                                            {{ sprintf('%02d:00', $i) }}
                                        </label>
                                        <input type="number" 
                                            name="siang_jam_{{ sprintf('%02d', $i) }}" 
                                            class="w-full text-center px-1.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" 
                                            value="0" min="0">
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Shift 3 (Malam) --}}
                        <div class="border border-indigo-200/80 dark:border-indigo-900/60 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                            <div class="px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/40 border-b border-indigo-200/60 dark:border-indigo-900/50 flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2">
                                    <i class="fas fa-moon text-indigo-500"></i>
                                    <span>SHIFT 3 (23:00 - 07:00)</span>
                                </span>
                                <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200 font-mono">
                                    Total Shift 3: <span id="total_malam">0</span> pcs
                                </span>
                            </div>
                            <div class="p-3 overflow-x-auto custom-scrollbar">
                                <div class="grid grid-cols-8 gap-2 min-w-[640px]">
                                    @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                    <div>
                                        <label class="block text-[10px] font-bold text-center text-slate-400 mb-1">
                                            {{ sprintf('%02d:00', $i) }}
                                        </label>
                                        <input type="number" 
                                            name="malam_jam_{{ sprintf('%02d', $i) }}" 
                                            class="w-full text-center px-1.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" 
                                            value="0" min="0">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                    <button type="button" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-750 transition-all cursor-pointer" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                        <i class="fas fa-save mr-1.5"></i> Simpan Planning
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/planning/create.js') }}"></script>
@endpush