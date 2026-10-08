@php
$user = Auth::user();
$role = $user->role;
$isRMStaff = $role === 'staff' && $user->departemen === 'Repair & Maintenance';
$isLeader = $role === 'leader' && !$isRMStaff;
@endphp

<!-- This form is intended to be loaded via AJAX into #editModalBody -->
<form id="editMesinForm" method="POST" action="{{ route('mesin.update', $mesin->id) }}" data-initial-reason="{{ $mesin->stop_reason }}">
    @csrf
    @method('PUT')

    <input type="hidden" id="initial_stop_reason" value="{{ $mesin->stop_reason }}">

    @if($isLeader)
    <div class="p-3 mb-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-medium flex items-center gap-2">
        <i class="fas fa-info-circle text-sm flex-shrink-0"></i>
        <span>Sebagai <b>Leader</b>, Anda diizinkan untuk memperbarui <b>Status Operasional</b>, <b>Pesan Alasan</b>, dan <b>Keterangan</b>.</span>
    </div>
    @endif

    <div class="space-y-4">
        {{-- Row 1: Nama Mesin & Brand --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama_mesin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Nama Mesin <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                    class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 {{ $isLeader ? 'bg-slate-100 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100' }} text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                    id="nama_mesin" name="nama_mesin" value="{{ $mesin->nama_mesin }}"
                    required {{ $isLeader ? 'readonly' : '' }}>
            </div>

            <div>
                <label for="brand_mesin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Brand Mesin <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                    class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 {{ $isLeader ? 'bg-slate-100 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100' }} text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                    id="brand_mesin" name="brand_mesin" value="{{ $mesin->brand_mesin }}"
                    required {{ $isLeader ? 'readonly' : '' }}>
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
                        class="form-control w-full pl-3.5 pr-14 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 {{ $isLeader ? 'bg-slate-100 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100' }} text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                        id="tonase_mesin" name="tonase_mesin" value="{{ $mesin->tonase_mesin }}"
                        step="0.01" min="0" max="999999" required {{ $isLeader ? 'readonly' : '' }}>
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
                    <option value="running" {{ $mesin->status == 'running' ? 'selected' : '' }}>Running (Beroperasi Normal)</option>
                    <option value="stop" {{ $mesin->status == 'stop' ? 'selected' : '' }}>Stand By (Berhenti Terencana)</option>
                    <option value="maintenance" {{ $mesin->status == 'maintenance' ? 'selected' : '' }}>Breakdown (Perbaikan / Kerusakan)</option>
                    <option value="offline" {{ $mesin->status == 'offline' ? 'selected' : '' }}>Off (Tidak Ada Jadwal / Mati)</option>
                </select>
            </div>
        </div>

        {{-- Dynamic Alasan / Pesan Status --}}
        <div id="stop_reason_wrapper" class="space-y-1.5" style="{{ in_array($mesin->status, ['stop', 'maintenance', 'offline']) ? '' : 'display: none;' }}">
            <label for="stop_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                Pesan / Alasan Status <span class="text-rose-500">*</span>
            </label>
            <select name="stop_reason" id="stop_reason" 
                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none cursor-pointer"
                {{ in_array($mesin->status, ['stop', 'maintenance', 'offline']) ? 'required' : '' }}>
                <option value="">-- Pilih Alasan --</option>
            </select>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-0">Rincian spesifik alasan kondisi mesin saat ini.</p>
        </div>

        {{-- Keterangan --}}
        <div>
            <label for="keterangan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                Keterangan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea name="keterangan" id="keterangan" rows="3"
                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none resize-none"
                placeholder="Tambahkan catatan pemeliharaan atau kondisi mesin...">{{ $mesin->keterangan }}</textarea>
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
            <i class="fas fa-check text-xs"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>