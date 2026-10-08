@extends('layouts.app')

@section('title', 'Jadwal Preventive Maintenance - Digital Production System')

@php
$user = Auth::user();
$userRole = $user ? strtolower($user->role) : '';
$isAdmin = $user && in_array($userRole, ['super_admin', 'kabag', 'manager_plant', 'leader']);

// Izin Spesifik
$canEditPlan = $isAdmin || ($user && $userRole === 'staff');
$canEditActual = $isAdmin || ($user && $userRole === 'staff');

// Hitung Pending Approvals jika ada izin
$pendingCount = 0;
if ($isAdmin) {
    $pendingCount = \App\Models\JadwalPemeliharaan::where('status_aktual', 'pending')->count();
}
@endphp

@push('styles')
<link href="{{ asset('css/jadwal-pemeliharaan.css') }}?v={{ filemtime(public_path('css/jadwal-pemeliharaan.css')) }}" rel="stylesheet">
@endpush

@section('content-header')
<!-- Hidden iframe for inline printing safely off-screen -->
<iframe id="printFrame" style="position: absolute; top: -9999px; left: -9999px; width: 0; height: 0; border: none; opacity: 0;" src=""></iframe>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 d-print-none">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/25 flex-shrink-0">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Jadwal Preventive Maintenance
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Tahun {{ $tahun }}</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span>{{ $mesins->count() }} Mesin Terjadwal</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Matriks rencana pemeliharaan berkala tahunan dan pelaporan realisasi teknisi R&M
            </p>
        </div>
    </div>

    {{-- Header Action Controls --}}
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Year Filter Form --}}
        <form method="GET" action="{{ route('jadwal-pemeliharaan.index') }}" class="m-0 flex items-center">
            <select name="tahun" 
                onchange="this.form.submit()" 
                class="year-filter-select rounded-xl font-bold text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-750 shadow-sm transition-all cursor-pointer outline-none"
                style="padding-right: 36px !important; padding-left: 14px !important; padding-top: 8px !important; padding-bottom: 8px !important; cursor: pointer;">
                @for ($i = date('Y') - 1; $i <= date('Y') + 3; $i++)
                    <option value="{{ $i }}" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100" {{ $tahun == $i ? 'selected' : '' }}>
                        Tahun {{ $i }}
                    </option>
                @endfor
            </select>
        </form>

        {{-- Export PDF Print --}}
        <button type="button" 
            data-url="{{ route('jadwal-pemeliharaan.print', ['tahun' => $tahun]) }}"
            onclick="printReport(this.getAttribute('data-url'))" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-500 active:bg-rose-700 text-white shadow-md shadow-rose-600/25 transition-all cursor-pointer">
            <i class="fas fa-print text-xs"></i>
            <span>Export PDF (Print)</span>
        </button>

        {{-- Approval Page Link (for Leaders / Admin) --}}
        @if($isAdmin)
        <a href="{{ route('jadwal-pemeliharaan.approval') }}" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white shadow-md shadow-amber-500/25 transition-all cursor-pointer">
            <i class="fas fa-check-double text-xs"></i>
            <span>Approval Realisasi</span>
            @if($pendingCount > 0)
            <span class="keep-white inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full text-[10px] font-black bg-white text-amber-800 ml-0.5 shadow-sm leading-none" style="background-color: #ffffff !important; color: #92400e !important;">
                {{ $pendingCount }}
            </span>
            @endif
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Quick KPI Cards --}}
    @php
        $totalPlanCount = 0;
        $totalActualCount = 0;
        $totalApprovedCount = 0;
        foreach($jadwalMap as $mMap) {
            foreach($mMap as $monMap) {
                foreach($monMap as $item) {
                    if(!empty($item->plan_tipe)) $totalPlanCount++;
                    if(!empty($item->aktual_tanggal)) $totalActualCount++;
                    if($item->status_aktual === 'approved') $totalApprovedCount++;
                }
            }
        }
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 d-print-none" id="kpiCardsContainer">
        {{-- Card 1: Total Mesin --}}
        <div class="kpi-card active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3.5 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between transition-all hover:shadow-md cursor-pointer select-none group"
            data-filter="all"
            title="Klik untuk menampilkan semua mesin">
            <div class="space-y-0.5 sm:space-y-1 min-w-0 flex-1 pr-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    Total Mesin
                </span>
                <div class="flex items-baseline gap-1.5 sm:gap-2 flex-wrap">
                    <span class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white leading-none kpi-counter" data-target="{{ $mesins->count() }}">
                        0
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Unit Aktif</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0 shadow-xs group-hover:scale-110 transition-transform">
                <i class="fas fa-industry"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 dark:bg-indigo-500"></div>
        </div>

        {{-- Card 2: Target Rencana (PLAN) --}}
        <div class="kpi-card bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3.5 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between transition-all hover:shadow-md cursor-pointer select-none group"
            data-filter="plan"
            title="Klik untuk filter mesin yang memiliki target plan">
            <div class="space-y-0.5 sm:space-y-1 min-w-0 flex-1 pr-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    Target Plan
                </span>
                <div class="flex items-baseline gap-1.5 sm:gap-2 flex-wrap">
                    <span class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 leading-none kpi-counter" data-target="{{ $totalPlanCount }}">
                        0
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Jadwal</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0 shadow-xs group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 hidden"></div>
        </div>

        {{-- Card 3: Realisasi Selesai (Disetujui) --}}
        <div class="kpi-card bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3.5 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between transition-all hover:shadow-md cursor-pointer select-none group"
            data-filter="approved"
            title="Klik untuk filter mesin dengan realisasi yang disetujui">
            <div class="space-y-0.5 sm:space-y-1 min-w-0 flex-1 pr-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                    Realisasi Approved
                </span>
                <div class="flex items-baseline gap-1.5 sm:gap-2 flex-wrap">
                    <span class="text-xl sm:text-2xl font-black font-mono text-sky-600 dark:text-sky-400 leading-none kpi-counter" data-target="{{ $totalApprovedCount }}">
                        0
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Tereksekusi</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0 shadow-xs group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-sky-500 hidden"></div>
        </div>

        {{-- Card 4: Total Input Aktual --}}
        <div class="kpi-card bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3.5 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between transition-all hover:shadow-md cursor-pointer select-none group"
            data-filter="actual"
            title="Klik untuk filter mesin yang sudah ada input aktual">
            <div class="space-y-0.5 sm:space-y-1 min-w-0 flex-1 pr-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                    Input Aktual
                </span>
                <div class="flex items-baseline gap-1.5 sm:gap-2 flex-wrap">
                    <span class="text-xl sm:text-2xl font-black font-mono text-amber-600 dark:text-amber-400 leading-none kpi-counter" data-target="{{ $totalActualCount }}">
                        0
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Entri</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base sm:text-lg flex-shrink-0 shadow-xs group-hover:scale-110 transition-transform">
                <i class="fas fa-wrench"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-amber-500 hidden"></div>
        </div>
    </div>

    {{-- Filter Status Notification Banner --}}
    <div id="kpiFilterBadge" class="hidden px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 flex items-center justify-between text-xs text-indigo-900 dark:text-indigo-200 d-print-none animate-fadeIn">
        <div class="flex items-center gap-2">
            <i class="fas fa-filter text-indigo-500"></i>
            <span>Filter Aktif: <strong id="kpiFilterLabel" class="font-bold">Semua Mesin</strong></span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-200/60 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                <span id="kpiFilterCount">0</span> Mesin Ditemukan
            </span>
        </div>
        <button type="button" id="kpiFilterResetBtn" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer">
            <i class="fas fa-times text-[10px]"></i> Reset Filter
        </button>
    </div>

    {{-- Main Yearly Maintenance Schedule Matrix Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3 d-print-none">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0 flex items-center gap-2">
                    <i class="fas fa-th text-emerald-500"></i>
                    <span>Matriks Tahunan Jadwal & Realisasi ({{ $tahun }})</span>
                </h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                    Klik pada sel PLAN atau ACTUAL untuk memperbarui jadwal dan mengunggah bukti realisasi
                </p>
            </div>
        </div>

        {{-- Month Quick-Navigation Bar & Horizontal Scroll Controls --}}
        <div class="px-3 sm:px-6 py-2 sm:py-2.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-2 sm:gap-3 overflow-x-auto custom-scrollbar d-print-none">
            <div class="flex items-center gap-1.5 flex-nowrap min-w-max">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mr-1 hidden sm:inline-flex items-center gap-1">
                    <i class="far fa-compass"></i>
                    <span>Lompat:</span>
                </span>
                <button type="button" class="month-nav-pill active" onclick="scrollToMonth('all', this)">Semua</button>
                @php
                    $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
                @endphp
                @foreach($monthNames as $mNum => $mShort)
                    <button type="button" class="month-nav-pill" onclick="scrollToMonth({{ $mNum }}, this)">
                        {{ $mShort }}
                    </button>
                @endforeach
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button type="button" onclick="scrollTable(-320)" class="w-8 h-8 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:border-indigo-300 dark:hover:text-indigo-400 flex items-center justify-center text-xs shadow-sm transition-all cursor-pointer border-0" title="Geser ke Kiri">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" onclick="scrollTable(320)" class="w-8 h-8 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:border-indigo-300 dark:hover:text-indigo-400 flex items-center justify-center text-xs shadow-sm transition-all cursor-pointer border-0" title="Geser ke Kanan">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Swipe Helper Banner --}}
        <div class="md:hidden px-4 py-1.5 bg-indigo-50/70 dark:bg-indigo-950/40 border-b border-indigo-100 dark:border-indigo-900/40 text-[11px] font-medium text-indigo-700 dark:text-indigo-300 flex items-center justify-between d-print-none">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-arrows-left-right text-[10px]"></i>
                <span>Geser tabel ke samping untuk melihat bulan & minggu</span>
            </span>
            <i class="fas fa-chevron-right text-[10px] animate-pulse"></i>
        </div>

        {{-- Matrix Table Container with Custom Scrollbar --}}
        <div class="table-responsive-pm">
            <table class="table-pm">
                <colgroup>
                    <col style="width: 44px; min-width: 44px;">
                    <col style="width: 150px; min-width: 150px;">
                    <col style="width: 76px; min-width: 76px;">
                    <col style="width: 74px; min-width: 74px;">
                    @for ($i = 0; $i < 48; $i++)
                    <col style="width: 42px; min-width: 42px;">
                    @endfor
                    <col style="width: 140px; min-width: 140px;">
                </colgroup>
                <thead>
                    <tr class="text-center">
                        <th rowspan="2" class="sticky-col align-middle text-center px-1 font-bold text-xs" style="width: 44px; min-width: 44px; max-width: 44px; left: 0;">No</th>
                        <th rowspan="2" class="sticky-col align-middle px-3 text-left font-bold text-xs" style="width: 150px; min-width: 150px; max-width: 150px; left: 44px;">Nama Mesin</th>
                        <th rowspan="2" class="sticky-col align-middle text-center px-1 font-bold text-xs" style="width: 76px; min-width: 76px; max-width: 76px; left: 194px;">
                            <span class="inline-block whitespace-nowrap">No Mesin</span>
                        </th>
                        <th rowspan="2" class="sticky-col sticky-col-last align-middle text-center px-1 font-bold text-xs" style="width: 74px; min-width: 74px; max-width: 74px; left: 270px;">Target</th>
                        
                        @for ($m = 1; $m <= 12; $m++)
                        <th colspan="4" class="month-header text-center" id="month-col-{{ $m }}" style="width: 168px; min-width: 168px; max-width: 168px;">
                            {{ Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                        </th>
                        @endfor
                        <th rowspan="2" class="align-middle px-3 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 font-bold text-xs" style="min-width: 140px; width: 140px;">Keterangan</th>
                    </tr>
                    <tr class="text-center">
                        @for ($m = 1; $m <= 12; $m++)
                            @for ($w = 1; $w <= 4; $w++)
                            <th class="week-header text-center {{ $w == 4 ? 'month-end-col' : '' }}" style="width: 42px; min-width: 42px; max-width: 42px;">
                                @php $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']; @endphp
                                {{ $romans[$w] }}
                            </th>
                            @endfor
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mesins as $index => $mesin)
                    @php
                        $mId = $mesin->id;
                        $mNama = $mesin->nama_mesin;
                        $mNo = substr($mNama, -2);

                        // Check flags for interactive KPI filtering
                        $hasPlan = false;
                        $hasApproved = false;
                        $hasActual = false;
                        if (isset($jadwalMap[$mId])) {
                            foreach ($jadwalMap[$mId] as $mon) {
                                foreach ($mon as $j) {
                                    if (!empty($j->plan_tipe)) {
                                        $hasPlan = true;
                                    }
                                    if (!empty($j->aktual_tanggal)) {
                                        $hasActual = true;
                                    }
                                    if (($j->status_aktual ?? '') === 'approved') {
                                        $hasApproved = true;
                                    }
                                }
                            }
                        }
                    @endphp
                    <!-- Baris PLAN -->
                    <tr class="row-plan machine-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
                        data-machine-id="{{ $mId }}"
                        data-has-plan="{{ $hasPlan ? '1' : '0' }}"
                        data-has-approved="{{ $hasApproved ? '1' : '0' }}"
                        data-has-actual="{{ $hasActual ? '1' : '0' }}">
                        <td rowspan="2" class="sticky-col align-middle text-center font-mono font-bold text-xs text-slate-500 dark:text-slate-400" style="left: 0; width: 44px; min-width: 44px; max-width: 44px;">
                            {{ $index + 1 }}
                        </td>
                        <td rowspan="2" class="sticky-col align-middle font-bold text-xs px-2.5 text-slate-900 dark:text-white" style="left: 44px; width: 150px; min-width: 150px; max-width: 150px; word-break: break-word;" title="{{ $mNama }}">
                            {{ $mNama }}
                        </td>
                        <td rowspan="2" class="sticky-col align-middle text-center font-mono font-bold text-xs text-slate-600 dark:text-slate-300" style="left: 194px; width: 76px; min-width: 76px; max-width: 76px;">
                            {{ $mNo }}
                        </td>
                        <td class="sticky-col sticky-col-last align-middle text-center p-1" style="left: 270px; width: 74px; min-width: 74px; max-width: 74px;">
                            <span class="inline-flex items-center justify-center w-[58px] py-0.5 rounded text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 shadow-xs">
                                PLAN
                            </span>
                        </td>
                        @for ($m = 1; $m <= 12; $m++)
                            @for ($w = 1; $w <= 4; $w++)
                            @php
                                $jadwal = $jadwalMap[$mId][$m][$w] ?? null;
                                $p = $jadwal ? $jadwal->plan_tipe : '';
                                $a = $jadwal ? $jadwal->aktual_tanggal : '';
                                $k = $jadwal ? $jadwal->keterangan : '';
                            @endphp
                            <td class="p-0 text-center edit-trigger {{ $w == 4 ? 'month-end-col' : '' }}"
                                style="width: 42px; min-width: 42px; max-width: 42px;"
                                data-mesin-id="{{ $mId }}"
                                data-mesin-nama="{{ $mNama }}"
                                data-bulan="{{ $m }}"
                                data-minggu="{{ $w }}"
                                data-type="plan"
                                data-plan="{{ $p }}"
                                data-actual="{{ $a }}"
                                data-keterangan="{{ $k }}">
                                @if(!empty($p))
                                    @php
                                        $pUpper = strtoupper(trim($p));
                                        $planBoxClass = 'box-plan-other';
                                        if($pUpper === 'S') $planBoxClass = 'box-plan-s';
                                        elseif($pUpper === 'M') $planBoxClass = 'box-plan-m';
                                        elseif($pUpper === 'O') $planBoxClass = 'box-plan-o';
                                    @endphp
                                    <span class="box-plan {{ $planBoxClass }}" title="{{ $p }}">{{ $p }}</span>
                                @endif
                            </td>
                            @endfor
                        @endfor
                        <td rowspan="2" class="align-middle px-3 text-xs text-slate-600 dark:text-slate-300">
                            @php
                                $latestKet = '';
                                if(isset($jadwalMap[$mId])) {
                                    foreach($jadwalMap[$mId] as $mon) {
                                        foreach($mon as $j) { if(!empty($j->keterangan)) $latestKet = $j->keterangan; }
                                    }
                                }
                            @endphp
                            {{ $latestKet ?: '-' }}
                        </td>
                    </tr>
                    <!-- Baris ACTUAL -->
                    <tr class="row-actual machine-row hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        data-machine-id="{{ $mId }}"
                        data-has-plan="{{ $hasPlan ? '1' : '0' }}"
                        data-has-approved="{{ $hasApproved ? '1' : '0' }}"
                        data-has-actual="{{ $hasActual ? '1' : '0' }}">
                        <td class="sticky-col sticky-col-last align-middle text-center p-1" style="left: 270px; width: 74px; min-width: 74px; max-width: 74px;">
                            <span class="inline-flex items-center justify-center w-[58px] py-0.5 rounded text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800 shadow-xs">
                                ACTUAL
                            </span>
                        </td>
                        @for ($m = 1; $m <= 12; $m++)
                            @for ($w = 1; $w <= 4; $w++)
                            @php
                                $jadwal = $jadwalMap[$mId][$m][$w] ?? null;
                                $p = $jadwal ? $jadwal->plan_tipe : '';
                                $a = $jadwal ? $jadwal->aktual_tanggal : '';
                                $k = $jadwal ? $jadwal->keterangan : '';
                                $jId = $jadwal ? $jadwal->id : '';
                                $bukti = $jadwal ? $jadwal->bukti : '';
                                $status = $jadwal ? $jadwal->status_aktual : '';
                                $approvedBy = $jadwal && $jadwal->approved_by ? ($jadwal->approver->nama ?? 'Unknown') : '';
                                $approvedAt = $jadwal && $jadwal->approved_at ? Carbon\Carbon::parse($jadwal->approved_at)->format('d/m/Y H:i') : '';
                            @endphp
                            <td class="p-0 text-center edit-trigger {{ $w == 4 ? 'month-end-col' : '' }}"
                                style="width: 42px; min-width: 42px; max-width: 42px;"
                                data-mesin-id="{{ $mId }}"
                                data-mesin-nama="{{ $mNama }}"
                                data-bulan="{{ $m }}"
                                data-minggu="{{ $w }}"
                                data-type="actual"
                                data-plan="{{ $p }}"
                                data-actual="{{ $a }}"
                                data-keterangan="{{ $k }}"
                                data-id="{{ $jId }}"
                                data-bukti="{{ $bukti }}"
                                data-status="{{ $status }}"
                                data-approved-by="{{ $approvedBy }}"
                                data-approved-at="{{ $approvedAt }}">
                                
                                @php
                                    $statusClass = '';
                                    if($status === 'approved') $statusClass = 'status-approved';
                                    elseif($status === 'rejected') $statusClass = 'status-rejected';
                                    elseif($status === 'pending') $statusClass = 'status-pending';
                                @endphp
                                
                                <div class="status-cell {{ $statusClass }}">
                                    @if(!empty($a))
                                    <span class="text-[9.5px] font-bold font-mono tracking-tight leading-none">{{ $a }}</span>
                                    @if($status === 'approved')
                                        <i class="fas fa-check-double text-[8px] text-emerald-600 dark:text-emerald-400 mt-0.5 leading-none"></i>
                                    @endif
                                    @endif
                                </div>
                            </td>
                            @endfor
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Guide & Legend Bar --}}
        <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs d-print-none">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="fas fa-info-circle text-indigo-500"></i> Legenda:
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                    <span class="box-plan box-plan-s text-[10px]">S</span> Small Maintenance
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                    <span class="box-plan box-plan-m text-[10px]">M</span> Medium Maintenance
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                    <span class="box-plan box-plan-o text-[10px]">O</span> Overhaul
                </span>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold border border-emerald-200/80 dark:border-emerald-800">
                    <i class="fas fa-check-double text-[9px]"></i> Approved
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold border border-amber-200/80 dark:border-amber-800">
                    <i class="fas fa-clock text-[9px]"></i> Pending
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 font-bold border border-rose-200/80 dark:border-rose-800">
                    <i class="fas fa-times text-[9px]"></i> Rejected
                </span>
            </div>
        </div>
    </div>

</div>

@include('jadwal-pemeliharaan.modal')

@endsection

@push('scripts')
<script>
    window.userRole = "{{ strtolower(auth()->user()->role) }}";
    window.userDept = "{{ auth()->user()->departemen }}";
    window.urlUpdateJadwal = "{{ route('jadwal-pemeliharaan.update-cell') }}";
    window.urlApprove = "{{ url('jadwal-pemeliharaan/approve') }}";
    window.urlReject = "{{ url('jadwal-pemeliharaan/reject') }}";
    
    // Fungsi untuk mencetak file PDF di latar belakang secara aman
    function printReport(url) {
        var printFrame = document.getElementById('printFrame');
        printFrame.onload = function() {
            setTimeout(function() {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (e) {
                    console.error('Gagal mencetak dari iframe:', e);
                }
            }, 300);
        };
        printFrame.src = url;
    }

    // Fungsi Navigasi Lompat Bulan dan Geser Horizontal Matrix
    function scrollToMonth(month, btn) {
        const container = document.querySelector('.table-responsive-pm');
        if (!container) return;
        
        document.querySelectorAll('.month-nav-pill').forEach(el => el.classList.remove('active'));
        if (btn) btn.classList.add('active');

        if (month === 'all') {
            container.scrollTo({ left: 0, behavior: 'smooth' });
            return;
        }

        const header = document.getElementById('month-col-' + month);
        if (header) {
            const isMobile = window.innerWidth <= 768;
            const frozenOffset = isMobile ? 0 : 344;
            const offset = header.offsetLeft - frozenOffset;
            container.scrollTo({ left: Math.max(0, offset), behavior: 'smooth' });
        }
    }

    function scrollTable(amount) {
        const container = document.querySelector('.table-responsive-pm');
        if (container) container.scrollBy({ left: amount, behavior: 'smooth' });
    }
</script>
<script src="{{ asset('js/jadwal-pemeliharaan.js') }}?v={{ filemtime(public_path('js/jadwal-pemeliharaan.js')) }}"></script>
@endpush