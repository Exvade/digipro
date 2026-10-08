@php
$user = Auth::user();
$dashboardRoute = route('menu');
if ($user) {
    if ($user->role === 'leader') {
        $dashboardRoute = route('leader.dashboard.inject');
    } elseif ($user->role === 'super_admin') {
        $dashboardRoute = route('super-admin.dashboard.inject');
    } elseif ($user->role === 'kabag') {
        $dashboardRoute = route('kabag.dashboard.inject');
    } elseif ($user->role === 'manager_plant') {
        $dashboardRoute = route('manager-plant.dashboard.inject');
    } elseif ($user->role === 'staff') {
        $dashboardRoute = route('staff.dashboard.inject');
    }
}

// Context Detection based on current route
$isMaintenanceModule = request()->routeIs(['jadwal-pemeliharaan.*']);
$isMesinModule       = request()->routeIs(['mesin.*', 'dashboard.inject.history']);
$isMasterUserModule  = request()->routeIs(['user.*', 'parts.*', 'meja.*', 'documents.*', 'moldshop.*']);
$isProduksiModule    = request()->routeIs(['input-produksi.*', 'ploting.*', 'jadwal-kerja.*', 'laporan-produksi.*', 'oee.*', 'approval-leader.*']);
$isPlanningModule    = request()->routeIs(['planning.*']);
$isChecksheetModule  = request()->routeIs(['checksheet.*', 'approval.*', 'report.*']);

$isLeaderAssy = $user && $user->role == 'leader' && $user->bagian == 'assy';
$isStaffMoldshop = $user && $user->role == 'staff' && $user->departemen == 'Moldshop';
$isAdminRole = $user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant']);
$canAccessMasterData = $user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'foreman']);
$isDocumentPage = request()->routeIs('documents.*');
@endphp

<aside id="mainSidebar" class="w-64 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-r border-slate-200/80 dark:border-slate-800 flex flex-col flex-shrink-0 transition-colors duration-300 z-30 min-h-screen">
    <!-- Sidebar Brand -->
    <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-xs font-black shadow-md shadow-indigo-500/20">
            DP
        </div>
        <a href="{{ $dashboardRoute }}" class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
            DIGIPRO <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 px-1.5 py-0.5 rounded">SYSTEM</span>
        </a>
    </div>

    <!-- Sidebar Navigation Content -->
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
        @if (!request()->routeIs(['*.dashboard.inject', '*.dashboard.assy']))

        {{-- CONTEXT 1: MAINTENANCE / PREVENTIVE --}}
        @if ($isMaintenanceModule)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Preventive</div>
                <div class="space-y-1">
                    <a href="{{ route('jadwal-pemeliharaan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('jadwal-pemeliharaan.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-tools text-sm {{ request()->routeIs('jadwal-pemeliharaan.index') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Jadwal Preventive</span>
                    </a>
                    @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']) || ($user->role === 'leader' && $user->departemen === 'Repair & Maintenance'))
                    <a href="{{ route('jadwal-pemeliharaan.approval') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('jadwal-pemeliharaan.approval') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span>Approval Preventive</span>
                        </div>
                        @php $pendingCount = \App\Models\JadwalPemeliharaan::where('status_aktual', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                        <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-extrabold rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    @endif
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>

        {{-- CONTEXT 2: DATA MESIN & RIWAYAT MESIN --}}
        @elseif ($isMesinModule)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Data Mesin</div>
                <div class="space-y-1">
                    <a href="{{ route('mesin.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('mesin.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-industry text-sm {{ request()->routeIs('mesin.index') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Data Mesin</span>
                    </a>
                    @if ($user && (in_array($user->role, ['super_admin', 'manager_plant', 'staff', 'leader', 'kabag']) && !($user->role == 'leader' && $user->bagian == 'assy')))
                    <a href="{{ route('dashboard.inject.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('dashboard.inject.history') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-history text-sm {{ request()->routeIs('dashboard.inject.history') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Riwayat Mesin</span>
                    </a>
                    @endif
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>

        {{-- CONTEXT 4: MASTER DATA / USER / DOKUMEN / MOLD --}}
        @elseif ($isMasterUserModule)
            @if ($canAccessMasterData)
                <div>
                    <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Master Data</div>

                    <div class="space-y-1">
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                        <a href="{{ route('user.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('user.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                            <i class="fas fa-users text-sm {{ request()->routeIs('user.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span>Data User</span>
                        </a>
                        @endif

                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'foreman']))
                        <a href="{{ route('parts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('parts.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                            <i class="fas fa-box text-sm {{ request()->routeIs('parts.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span>Data Part</span>
                        </a>
                        @endif

                        <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('documents.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                            <i class="fas fa-file-contract text-sm {{ request()->routeIs('documents.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span>Data Dokumen</span>
                        </a>

                        @if ($user->role === 'super_admin' || $isAdminRole || $isStaffMoldshop)
                        <a href="{{ route('moldshop.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('moldshop.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                            <i class="fas fa-tools text-sm {{ request()->routeIs('moldshop.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span>Data Mold</span>
                        </a>
                        @endif

                        @if ($isLeaderAssy)
                        <a href="{{ route('meja.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('meja.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                            <i class="fas fa-table text-sm {{ request()->routeIs('meja.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span>Data Meja</span>
                        </a>
                        @endif
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                            <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                                <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                                <span>Menu Utama</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        {{-- CONTEXT: PRODUKSI (Input Hasil Produksi, Control Leader, Jadwal Kerja, Laporan, OEE) --}}
        @elseif ($isProduksiModule)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Produksi</div>
                <div class="space-y-1">
                    {{-- 1. Input Hasil Produksi --}}
                    @if ($user && (!in_array($user->role, ['staff', 'manager_plant']) || in_array($user->role, ['super_admin', 'kabag', 'leader', 'foreman', 'operator'])))
                    <a href="{{ route('input-produksi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs(['input-produksi.*', 'approval-leader.*']) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-clipboard-list text-sm {{ request()->routeIs(['input-produksi.*', 'approval-leader.*']) ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Input Hasil Produksi</span>
                    </a>
                    @endif

                    {{-- 2. Control Leader / Ploting --}}
                    @if ($user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'foreman', 'leader']))
                    <a href="{{ route('ploting.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('ploting.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-users-cog text-sm {{ request()->routeIs('ploting.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Control Leader</span>
                    </a>
                    @endif

                    {{-- 3. Jadwal Kerja --}}
                    @if ($user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman', 'operator']) && (in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant', 'Repair & Maintenance']) || in_array($user->bagian, ['inject', 'assy']))))
                    <a href="{{ route('jadwal-kerja.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('jadwal-kerja.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-calendar-alt text-sm {{ request()->routeIs('jadwal-kerja.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Jadwal Kerja</span>
                    </a>
                    @endif

                    {{-- 4. Laporan Produksi --}}
                    @if ($user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'foreman']) && in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant']))
                    <a href="{{ route('laporan-produksi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('laporan-produksi.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-file-invoice text-sm {{ request()->routeIs('laporan-produksi.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Laporan Produksi</span>
                    </a>
                    @endif

                    {{-- 5. OEE Monitoring --}}
                    @if ($user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                    <a href="{{ route('oee.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('oee.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-chart-line text-sm {{ request()->routeIs('oee.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>OEE Monitoring</span>
                    </a>
                    @endif

                    {{-- Link ke Menu Utama --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>

        {{-- CONTEXT: PLANNING (Planning Produksi & Approval Planning) --}}
        @elseif ($isPlanningModule)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Planning</div>
                <div class="space-y-1">
                    @if ($user && ($user->role === 'super_admin' || in_array($user->role, ['kabag', 'staff', 'manager_plant', 'leader', 'foreman'])))
                    <a href="{{ route('planning.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ (request()->routeIs('planning.*') && !request()->routeIs('planning.approval.*')) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-calendar-check text-sm {{ (request()->routeIs('planning.*') && !request()->routeIs('planning.approval.*')) ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Planning Produksi</span>
                    </a>
                    @endif

                    @if ($user && ($user->role === 'super_admin' || $user->role === 'manager_plant' || ($user->role === 'kabag' && in_array($user->departemen, ['PPIC', 'Production Planning Inventory Control', 'Production Planning & Inventory Control']))))
                    <a href="{{ route('planning.approval.page') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('planning.approval.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-sm {{ request()->routeIs('planning.approval.*') ? 'text-white' : 'text-emerald-500' }}"></i>
                            <span>Approval Planning</span>
                        </div>
                        @php 
                            $pendingPlanningCount = \App\Models\PlanningProduksi::where('status_approval', 'pending')->count(); 
                        @endphp
                        @if($pendingPlanningCount > 0)
                        <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-extrabold rounded-full">{{ $pendingPlanningCount }}</span>
                        @endif
                    </a>
                    @endif

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>

        {{-- CONTEXT 10: CHECKSHEET / APPROVAL / REPORT --}}
        @elseif ($isChecksheetModule)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Checksheet</div>
                <div class="space-y-1">
                    <a href="{{ route('checksheet.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('checksheet.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-clipboard-list text-sm {{ request()->routeIs('checksheet.index') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Data Checksheet</span>
                    </a>
                    <a href="{{ route('approval.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('approval.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-check-double text-sm {{ request()->routeIs('approval.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Approval</span>
                    </a>
                    <a href="{{ route('report.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('report.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        <i class="fas fa-chart-pie text-sm {{ request()->routeIs('report.index') ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span>Report</span>
                    </a>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>

        {{-- FALLBACK GENERAL / MENU ROUTE --}}
        @else
            @if ($canAccessMasterData)
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2">Master Data</div>
                <div class="space-y-1">
                    @if ($isAdminRole)
                    <a href="{{ route('user.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="fas fa-users text-sm text-slate-400 dark:text-slate-500"></i>
                        <span>Data User</span>
                    </a>
                    <a href="{{ route('parts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="fas fa-box text-sm text-slate-400 dark:text-slate-500"></i>
                        <span>Data Part</span>
                    </a>
                    @endif
                    <a href="{{ route('mesin.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="fas fa-industry text-sm text-slate-400 dark:text-slate-500"></i>
                        <span>Data Mesin</span>
                    </a>
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="fas fa-file-contract text-sm text-slate-400 dark:text-slate-500"></i>
                        <span>Data Dokumen</span>
                    </a>
                    @if ($user->role === 'super_admin' || $isAdminRole || $isStaffMoldshop)
                    <a href="{{ route('moldshop.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="fas fa-tools text-sm text-slate-400 dark:text-slate-500"></i>
                        <span>Data Mold</span>
                    </a>
                    @endif
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                        <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                            <i class="fas fa-home text-sm text-slate-400 dark:text-slate-500"></i>
                            <span>Menu Utama</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        @endif

        @endif
    </div>
</aside>