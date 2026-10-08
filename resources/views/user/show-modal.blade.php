<div class="user-detail-container space-y-5">
    {{-- Profile Hero Card --}}
    <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-50/80 via-slate-50 to-purple-50/50 dark:from-slate-800/80 dark:via-slate-900 dark:to-indigo-950/40 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center sm:items-start gap-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-indigo-500/25 flex-shrink-0">
            {{ strtoupper(substr($user->nama, 0, 1)) }}
        </div>
        <div class="text-center sm:text-left flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight mb-0 truncate">
                    {{ $user->nama }}
                </h3>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold self-center sm:self-auto
                    {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                {{ '@' . $user->username }}
            </div>

            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                {{-- Role Badge --}}
                @switch($user->role)
                    @case('super_admin')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            SUPER ADMIN
                        </span>
                        @break
                    @case('leader')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            LEADER
                        </span>
                        @break
                    @case('operator')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            OPERATOR
                        </span>
                        @break
                    @case('foreman')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            FOREMAN
                        </span>
                        @break
                    @case('kabag')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            KABAG
                        </span>
                        @break
                    @case('staff')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            STAFF
                        </span>
                        @break
                    @case('manager_plant')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            MANAGER PLANT
                        </span>
                        @break
                    @default
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ strtoupper(str_replace('_', ' ', $user->role)) }}
                        </span>
                @endswitch

                {{-- Bagian Pill --}}
                @if($user->bagian)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold
                        {{ $user->bagian == 'inject' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60' }}">
                        {{ strtoupper($user->bagian) }}
                    </span>
                @endif

                {{-- Grup Pill --}}
                @if($user->grup_id && $user->grup)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        <i class="fas fa-layer-group text-[9px] mr-1 text-slate-400"></i>{{ $user->grup->nama_grup }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- NIK --}}
        <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0">
                <i class="fas fa-id-badge"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Nomor Induk Karyawan (NIK)</div>
                <div class="text-xs font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->nik ?? '-' }}</div>
            </div>
        </div>

        {{-- Departemen --}}
        <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm flex-shrink-0">
                <i class="fas fa-building"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Departemen</div>
                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->departemen ?? '-' }}</div>
            </div>
        </div>

        {{-- Email --}}
        <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 flex items-center gap-3 sm:col-span-2">
            <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/70 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm flex-shrink-0">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Alamat Email</div>
                <div class="text-xs font-mono font-medium text-slate-800 dark:text-slate-200 mt-0.5 truncate">{{ $user->email ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Activity & Audit Log --}}
    <div class="p-4 rounded-xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-3">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300">
            <i class="fas fa-clock-rotate-left text-indigo-500"></i>
            <span>Riwayat Aktivitas Akun</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="space-y-0.5">
                <div class="text-[11px] text-slate-400 dark:text-slate-500">Terdaftar Pada</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">
                    {{ $user->created_at ? $user->created_at->format('d M Y - H:i') : '-' }} WIB
                </div>
            </div>
            <div class="space-y-0.5">
                <div class="text-[11px] text-slate-400 dark:text-slate-500">Terakhir Login</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">
                    @if($user->last_login_at)
                        {{ $user->last_login_at->format('d M Y - H:i') }} WIB
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 block">({{ $user->last_login_at->diffForHumans() }})</span>
                    @else
                        <span class="text-slate-400 dark:text-slate-500 italic">Belum pernah login</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>