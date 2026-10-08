<div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
    <!-- Karyawan -->
    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
            <i class="fas fa-user"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Nama Karyawan</div>
            <div class="font-bold text-slate-800 dark:text-slate-100 text-sm truncate mt-0.5">{{ $jadwal->user->nama ?? '-' }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 capitalize mt-0.5">
                {{ $jadwal->user->role ?? '-' }} • {{ $jadwal->user->departemen ?? 'Produksi' }}
            </div>
        </div>
    </div>

    <!-- Tanggal & Hari -->
    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tanggal & Hari</div>
            <div class="font-bold text-slate-800 dark:text-slate-100 text-sm mt-0.5">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd') }}</div>
        </div>
    </div>

    <!-- Shift & Jam Kerja -->
    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
            <i class="fas fa-clock"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Shift & Jam Kerja</div>
            <div class="flex items-center gap-2 mt-1">
                @php
                $shiftColors = [
                    'pagi' => 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300/40 dark:border-emerald-700/40',
                    'siang' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border-amber-300/40 dark:border-amber-700/40',
                    'malam' => 'bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-indigo-300/40 dark:border-indigo-700/40',
                    'off' => 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300/40 dark:border-slate-700/40',
                ];
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black border uppercase tracking-wider {{ $shiftColors[$jadwal->shift] ?? $shiftColors['off'] }}">
                    {{ ucfirst($jadwal->shift) }}
                </span>
            </div>
            <div class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold mt-1">
                @if ($jadwal->shift !== 'off')
                    {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }} ({{ $jadwal->durasi_kerja }} jam)
                @else
                    Libur / Off
                @endif
            </div>
        </div>
    </div>

    <!-- Bagian & Grup -->
    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Bagian & Grup</div>
            <div class="font-bold text-slate-800 dark:text-slate-100 text-sm mt-0.5">
                {{ $jadwal->user ? ucfirst($jadwal->user->bagian ?? '-') : '-' }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Grup: {{ $jadwal->user && $jadwal->user->grup ? strtoupper($jadwal->user->grup->nama_grup) : '-' }}
            </div>
        </div>
    </div>

    <!-- Status & Keterangan -->
    <div class="col-span-1 sm:col-span-2 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status:</span>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black border uppercase tracking-wider {{ ($jadwal->status ?? '') === 'aktif' ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300/40 dark:border-emerald-700/40' : 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-300/40 dark:border-rose-700/40' }}">
                {{ ucfirst($jadwal->status ?? '-') }}
            </span>
        </div>
        @if($jadwal->keterangan)
        <div class="text-xs text-slate-600 dark:text-slate-300 italic">
            "{{ $jadwal->keterangan }}"
        </div>
        @endif
    </div>
</div>
