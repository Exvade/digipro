{{-- ==================== SUPER ADMIN / KABAG / FOREMAN / LEADER VIEW - APPROVAL DASHBOARD ==================== --}}

@php
$user = Auth::user();
$isLeaderInject = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject');
$isLeaderAssy = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy');
@endphp

{{-- Unified Toolbar: Filter Periode, Bagian Selector, dan Status Sync --}}
<div class="approval-toolbar border rounded-2xl p-3.5 sm:p-4 shadow-sm transition-all duration-200">
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3.5">
        {{-- Form Filter Periode --}}
        <form id="dateFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <div class="flex items-center gap-2 filter-date-wrapper px-3.5 py-2 rounded-xl border">
                <i class="fas fa-calendar-alt text-indigo-500 text-xs"></i>
                <span class="text-[11px] font-bold filter-date-label uppercase tracking-wider select-none">Periode:</span>
                <input type="date" class="filter-date-input text-xs font-bold outline-none cursor-pointer" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important;">
                <span class="text-xs font-bold filter-date-sep px-1 select-none">s/d</span>
                <input type="date" class="filter-date-input text-xs font-bold outline-none cursor-pointer" id="end_date" name="end_date" value="{{ date('Y-m-d') }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important;">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btnApplyDateFilter" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <i class="fas fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>
                <button type="button" id="btnResetDateFilter" class="btn-reset-filter px-3.5 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <i class="fas fa-sync-alt text-[10px]"></i>
                    <span>Reset</span>
                </button>
            </div>
        </form>

        {{-- Controls Kanan: Bagian Selector & Auto Sync Indicator --}}
        <div class="flex items-center justify-between sm:justify-end gap-3 flex-wrap">
            @if(!$isLeaderInject && !$isLeaderAssy)
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider hidden sm:inline">
                    Bagian:
                </span>
                <div class="nav nav-pills" id="bagianTabsApprovalLeader" role="tablist">
                    <a class="nav-link active" id="all-tab-approval-leader" href="#" data-bagian="all">
                        <i class="fas fa-list-ul text-[10px]"></i>
                        <span>Semua</span>
                    </a>
                    <a class="nav-link" id="inject-tab-approval-leader" href="#" data-bagian="inject">
                        <i class="fas fa-cog text-[10px]"></i>
                        <span>Inject</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Status Approval Tabs --}}
<div class="flex items-center justify-between flex-wrap gap-3 pt-2">
    <ul class="nav nav-tabs" id="approvalTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                <i class="fas fa-clock text-amber-500"></i>
                <span>Pending Approval</span>
                <span class="badge" id="pendingCount">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span>Approved</span>
                <span class="badge" id="approvedCount">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
                <i class="fas fa-times-circle text-rose-500"></i>
                <span>Rejected</span>
                <span class="badge" id="rejectedCount">0</span>
            </a>
        </li>
    </ul>
</div>

{{-- Tabs Content --}}
<div class="tab-content" id="approvalTabsContent">
    {{-- ==================== TAB PENDING ==================== --}}
    <div class="tab-pane fade show active" id="pending" role="tabpanel">
        {{-- Shift Pill Filter Bar --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="flex items-center gap-1 p-1 rounded-xl border shift-filter-group" data-target-tab="pending">
                <button type="button" class="shift-filter-btn active px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm" data-shift="all">
                    Semua Shift
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Shift 1 (Pagi)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300" id="filterPendingShift1Count">0</span>
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift2">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <span>Shift 2 (Siang)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-sky-100 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300" id="filterPendingShift2Count">0</span>
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift3">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>Shift 3 (Malam)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300" id="filterPendingShift3Count">0</span>
                </button>
            </div>
        </div>

        {{-- Shift 1 (Pagi) Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift1">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-sun text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 1 (Pagi)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300/40 dark:border-amber-700/40" id="pendingShift1Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="pendingTableShift1" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 9%">Tanggal</th>
                                <th style="width: 6%">Bagian</th>
                                <th style="width: 13%">Operator</th>
                                <th style="width: 6%">Grup</th>
                                <th style="width: 14%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Shift 2 (Siang) Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift2">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-cloud-sun text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 2 (Siang)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-sky-100 text-sky-800 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-300/40 dark:border-sky-700/40" id="pendingShift2Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="pendingTableShift2" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 9%">Tanggal</th>
                                <th style="width: 6%">Bagian</th>
                                <th style="width: 13%">Operator</th>
                                <th style="width: 6%">Grup</th>
                                <th style="width: 14%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Shift 3 (Malam) Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift3">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-moon text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 3 (Malam)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-300/40 dark:border-indigo-700/40" id="pendingShift3Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="pendingTableShift3" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 9%">Tanggal</th>
                                <th style="width: 6%">Bagian</th>
                                <th style="width: 13%">Operator</th>
                                <th style="width: 6%">Grup</th>
                                <th style="width: 14%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TAB APPROVED ==================== --}}
    <div class="tab-pane fade" id="approved" role="tabpanel">
        {{-- Shift Pill Filter Bar --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="flex items-center gap-1 p-1 rounded-xl border shift-filter-group" data-target-tab="approved">
                <button type="button" class="shift-filter-btn active px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm" data-shift="all">
                    Semua Shift
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Shift 1 (Pagi)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300" id="filterApprovedShift1Count">0</span>
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift2">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <span>Shift 2 (Siang)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-teal-100 dark:bg-teal-950/80 text-teal-700 dark:text-teal-300" id="filterApprovedShift2Count">0</span>
                </button>
                <button type="button" class="shift-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5" data-shift="shift3">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    <span>Shift 3 (Malam)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-cyan-100 dark:bg-cyan-950/80 text-cyan-700 dark:text-cyan-300" id="filterApprovedShift3Count">0</span>
                </button>
            </div>
        </div>

        {{-- Shift 1 Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift1">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-sun text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 1 (Pagi)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300/40 dark:border-emerald-700/40" id="approvedShift1Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="approvedTableShift1" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 8%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 12%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 13%">Part</th>
                                <th style="width: 7%">Mold/Meja</th>
                                <th style="width: 7%">Mesin/Meja</th>
                                <th style="width: 4%">OK</th>
                                <th style="width: 4%">NG</th>
                                <th style="width: 4%">Total</th>
                                <th style="width: 4%">Target</th>
                                <th style="width: 4%">Eff</th>
                                <th style="width: 9%">Approved By</th>
                                <th style="width: 8%">Approved At</th>
                                <th style="width: 6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Shift 2 Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift2">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-cloud-sun text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 2 (Siang)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-teal-100 text-teal-800 dark:bg-teal-950/70 dark:text-teal-300 border border-teal-300/40 dark:border-teal-700/40" id="approvedShift2Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="approvedTableShift2" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 8%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 12%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 13%">Part</th>
                                <th style="width: 7%">Mold/Meja</th>
                                <th style="width: 7%">Mesin/Meja</th>
                                <th style="width: 4%">OK</th>
                                <th style="width: 4%">NG</th>
                                <th style="width: 4%">Total</th>
                                <th style="width: 4%">Target</th>
                                <th style="width: 4%">Eff</th>
                                <th style="width: 9%">Approved By</th>
                                <th style="width: 8%">Approved At</th>
                                <th style="width: 6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Shift 3 Section --}}
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4 transition-all" data-shift-card="shift3">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-moon text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Shift 3 (Malam)
                    </h5>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-cyan-100 text-cyan-800 dark:bg-cyan-950/70 dark:text-cyan-300 border border-cyan-300/40 dark:border-cyan-700/40" id="approvedShift3Count">0</span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500 transition-transform duration-200 shift-collapse-icon"></i>
                </div>
            </div>
            <div class="shift-card-body p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="approvedTableShift3" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 8%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 12%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 13%">Part</th>
                                <th style="width: 7%">Mold/Meja</th>
                                <th style="width: 7%">Mesin/Meja</th>
                                <th style="width: 4%">OK</th>
                                <th style="width: 4%">NG</th>
                                <th style="width: 4%">Total</th>
                                <th style="width: 4%">Target</th>
                                <th style="width: 4%">Eff</th>
                                <th style="width: 9%">Approved By</th>
                                <th style="width: 8%">Approved At</th>
                                <th style="width: 6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TAB REJECTED ==================== --}}
    <div class="tab-pane fade" id="rejected" role="tabpanel">
        <div class="shift-card-section border rounded-2xl shadow-sm overflow-hidden mb-4">
            <div class="shift-card-header px-4 py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-times-circle text-[11px]"></i>
                    </div>
                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                        Data Rejected
                    </h5>
                </div>
            </div>
            <div class="p-3">
                <div class="table-responsive">
                    <table class="table approval-table align-middle" id="rejectedTable" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 4%">No</th>
                                <th style="width: 8%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 12%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 13%">Part</th>
                                <th style="width: 7%">Mold/Meja</th>
                                <th style="width: 7%">Mesin/Meja</th>
                                <th style="width: 4%">OK</th>
                                <th style="width: 4%">NG</th>
                                <th style="width: 4%">Total</th>
                                <th style="width: 13%">Alasan</th>
                                <th style="width: 9%">Rejected By</th>
                                <th style="width: 8%">Rejected At</th>
                                <th style="width: 6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('approval-leader.modals')

<div id="approval-leader-config"
    data-is-leader-inject="{{ $isLeaderInject ? 'true' : 'false' }}"
    data-is-leader-assy="{{ $isLeaderAssy ? 'true' : 'false' }}">
</div>

@push('scripts')
{{-- Tab Filter & Shift Controls Script --}}
<script>
    $(document).ready(function() {
        const config = $('#approval-leader-config');
        const isLeaderInject = config.data('is-leader-inject') === true;
        const isLeaderAssy = config.data('is-leader-assy') === true;

        // Hanya jalankan jika tab filter ada (bukan leader dengan bagian spesifik)
        if ($('#bagianTabsApprovalLeader').length > 0) {
            $('#bagianTabsApprovalLeader .nav-link').on('click', function(e) {
                e.preventDefault();
                const bagian = $(this).data('bagian');

                // Update active tab
                $('#bagianTabsApprovalLeader .nav-link').removeClass('active');
                $(this).addClass('active');

                if (typeof setBagianFilter === 'function') {
                    setBagianFilter(bagian);
                }
            });
        } else {
            // Untuk leader dengan bagian spesifik, set filter otomatis
            if (isLeaderInject) {
                if (typeof setBagianFilter === 'function') {
                    setBagianFilter('inject');
                }
            } else if (isLeaderAssy) {
                if (typeof setBagianFilter === 'function') {
                    setBagianFilter('assy');
                }
            }
        }

        // Shift Filter Pill Switcher (Semua Shift, Shift 1, Shift 2, Shift 3)
        $(document).on('click', '.shift-filter-btn', function() {
            const btn = $(this);
            const targetGroup = btn.closest('.shift-filter-group');
            const targetTab = targetGroup.data('target-tab'); // 'pending' or 'approved'
            const shift = btn.data('shift'); // 'all', 'shift1', 'shift2', 'shift3'

            targetGroup.find('.shift-filter-btn').removeClass('active');
            btn.addClass('active');

            const container = $(`#${targetTab}`);
            if (shift === 'all') {
                container.find('.shift-card-section').removeClass('hidden').slideDown(150);
            } else {
                container.find('.shift-card-section').addClass('hidden').slideUp(0);
                container.find(`[data-shift-card="${shift}"]`).removeClass('hidden').slideDown(150);
            }

            // Adjust DataTables in visible section
            setTimeout(() => {
                if (targetTab === 'pending') {
                    if (typeof pendingTableShift1Table !== 'undefined' && pendingTableShift1Table) pendingTableShift1Table.columns.adjust();
                    if (typeof pendingTableShift2Table !== 'undefined' && pendingTableShift2Table) pendingTableShift2Table.columns.adjust();
                    if (typeof pendingTableShift3Table !== 'undefined' && pendingTableShift3Table) pendingTableShift3Table.columns.adjust();
                } else if (targetTab === 'approved') {
                    if (typeof approvedTableShift1Table !== 'undefined' && approvedTableShift1Table) approvedTableShift1Table.columns.adjust();
                    if (typeof approvedTableShift2Table !== 'undefined' && approvedTableShift2Table) approvedTableShift2Table.columns.adjust();
                    if (typeof approvedTableShift3Table !== 'undefined' && approvedTableShift3Table) approvedTableShift3Table.columns.adjust();
                }
            }, 160);
        });

        // Clickable header to collapse / expand shift section
        $(document).on('click', '.shift-card-header', function() {
            const card = $(this).closest('.shift-card-section');
            const body = card.find('.shift-card-body');
            const icon = $(this).find('.shift-collapse-icon');

            body.slideToggle(180);
            icon.toggleClass('rotate-180');
        });
    });
</script>
@endpush