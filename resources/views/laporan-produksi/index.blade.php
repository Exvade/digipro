@extends('layouts.app')

@section('title', 'Laporan Produksi - Digital Production System')

@push('styles')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
{{-- Custom Tailwind Styles for Laporan Produksi --}}
<link rel="stylesheet" href="{{ asset('css/laporan-produksi-index.css') }}?v={{ filemtime(public_path('css/laporan-produksi-index.css')) }}">
@endpush

@php
    $user = Auth::user();
    $isLeaderInject = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject');
    $isLeaderAssy = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy');

    // Aggregate KPI Statistics
    $totalBatch = $dataProduksi->count();
    $totalPlan = $dataProduksi->sum(function($d) { return $d->planning->qty_planning_produksi ?? 0; });
    $totalOk = $dataProduksi->sum('ok_count');
    $totalNg = $dataProduksi->sum('ng_count');
    $totalActual = $totalOk + $totalNg;
    $achievementRate = $totalPlan > 0 ? round(($totalOk / $totalPlan) * 100, 1) : 0;
    $defectRate = $totalActual > 0 ? round(($totalNg / $totalActual) * 100, 2) : 0;
@endphp

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-600 via-rose-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-rose-500/25 flex-shrink-0">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Laporan Produksi
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Approved Only</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Rekapitulasi dan riwayat hasil produksi yang telah diverifikasi dan disetujui (approved)
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if(request('tanggal_awal') && request('tanggal_akhir'))
        <a href="{{ route('laporan-produksi.cetak', [
                    'tanggal_awal' => request('tanggal_awal'),
                    'tanggal_akhir' => request('tanggal_akhir'),
                    'grup_filter' => request('grup_filter'),
                    'mesin_filter' => request('mesin_filter')
                ]) }}"
            class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
            target="_blank"
            title="Cetak PDF Periode"
            data-toggle="tooltip">
            <i class="fas fa-file-pdf text-sm"></i>
        </a>
        @else
        <a href="{{ route('laporan-produksi.cetak', [
                    'grup_filter' => request('grup_filter'),
                    'mesin_filter' => request('mesin_filter')
                ]) }}"
            class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
            target="_blank"
            title="Cetak Semua PDF"
            data-toggle="tooltip">
            <i class="fas fa-file-pdf text-sm"></i>
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Executive Summary KPI Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Batch Approved --}}
        <div class="kpi-card kpi-card-indigo active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="all" title="Klik untuk menampilkan semua urutan default">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Batch Approved
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-slate-900 dark:text-white" data-target="{{ $totalBatch }}">
                        {{ number_format($totalBatch) }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">batch</span>
                </div>
                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    Laporan siap cetak & arsip
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center text-xl flex-shrink-0 kpi-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-b-2xl"></div>
        </div>

        {{-- Card 2: Total Target Planning --}}
        <div class="kpi-card kpi-card-sky bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="plan" title="Klik untuk mengurutkan berdasarkan Target Plan terbesar">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Target Plan
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-sky-600 dark:text-sky-400" data-target="{{ $totalPlan }}">
                        {{ number_format($totalPlan) }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">pcs</span>
                </div>
                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    Target produksi kumulatif
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900/40 flex items-center justify-center text-xl flex-shrink-0 kpi-icon">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-sky-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Card 3: Total Output OK & Achievement Rate --}}
        <div class="kpi-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="ok" title="Klik untuk mengurutkan berdasarkan Output OK terbanyak">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Output OK
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-target="{{ $totalOk }}">
                        {{ number_format($totalOk) }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">pcs</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-chart-line text-[10px]"></i>
                    <span>Pencapaian: <span class="kpi-counter" data-target="{{ $achievementRate }}" data-decimal="true">{{ $achievementRate }}</span>%</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center text-xl flex-shrink-0 kpi-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Card 4: Total Reject / NG & Defect Rate --}}
        <div class="kpi-card kpi-card-rose bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="ng" title="Klik untuk mengurutkan batch dengan Defect / NG tertinggi">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Reject / NG
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-rose-600 dark:text-rose-400" data-target="{{ $totalNg }}">
                        {{ number_format($totalNg) }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">pcs</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-bold {{ $defectRate > 2 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">
                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                    <span>Rasio NG: <span class="kpi-counter" data-target="{{ $defectRate }}" data-decimal="true">{{ $defectRate }}</span>%</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 flex items-center justify-center text-xl flex-shrink-0 kpi-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-600 rounded-b-2xl hidden"></div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-circle text-sm"></i>
            </div>
            <span class="text-xs font-semibold">{{ session('error') }}</span>
        </div>
        <button type="button" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(request('tanggal_awal') && request('tanggal_akhir'))
    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle text-sm"></i>
            </div>
            <div class="text-xs">
                <span>Menampilkan data periode:</span>
                <strong class="mx-1 text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse(request('tanggal_awal'))->format('d-m-Y') }}</strong>
                <span>s/d</span>
                <strong class="mx-1 text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d-m-Y') }}</strong>
                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 ml-2">
                    {{ $dataProduksi->count() }} Data Ditemukan
                </span>
            </div>
        </div>
        <a href="{{ route('laporan-produksi.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-xs font-bold inline-flex items-center gap-1">
            <span>Hapus Filter</span>
            <i class="fas fa-times"></i>
        </a>
    </div>
    @endif

    {{-- Tab Filter Section (Hanya tampil jika bukan leader spesifik) --}}
    @if(!$isLeaderInject && !$isLeaderAssy)
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-1.5 shadow-sm inline-flex flex-wrap items-center gap-1">
        <ul class="nav nav-pills flex items-center gap-1 m-0 p-0 list-none bagian-tabs" id="bagianTabsLaporan" role="tablist">
            <li class="nav-item">
                <a class="nav-link active px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="all-tab-laporan" href="#" data-bagian="all">
                    <i class="fas fa-layer-group text-[11px]"></i>
                    <span>Semua Bagian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="inject-tab-laporan" href="#" data-bagian="inject">
                    <i class="fas fa-cog text-[11px]"></i>
                    <span>Bagian Inject</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="assy-tab-laporan" href="#" data-bagian="assy">
                    <i class="fas fa-tools text-[11px]"></i>
                    <span>Bagian Assembly</span>
                </a>
            </li>
        </ul>
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm transition-all duration-200">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs font-bold">
                    <i class="fas fa-filter text-[11px]"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider mb-0">
                        Filter & Parameter Laporan
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">
                        Saring data berdasarkan rentang tanggal, grup kerja, atau mesin
                    </p>
                </div>
            </div>

            {{-- Quick Presets --}}
            <div class="hidden sm:flex items-center gap-1.5">
                <button type="button" data-preset="today" class="btn-quick-date px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors">
                    Hari Ini
                </button>
                <button type="button" data-preset="7days" class="btn-quick-date px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors">
                    7 Hari Terakhir
                </button>
                <button type="button" data-preset="month" class="btn-quick-date px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors">
                    Bulan Ini
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('laporan-produksi.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Tanggal Awal
                    </label>
                    <div class="relative">
                        <input type="date" name="tanggal_awal" class="w-full h-10 px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all outline-none" value="{{ request('tanggal_awal') }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Tanggal Akhir
                    </label>
                    <div class="relative">
                        <input type="date" name="tanggal_akhir" class="w-full h-10 px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all outline-none" value="{{ request('tanggal_akhir') }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Filter Grup
                    </label>
                    <select class="w-full h-10 px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all cursor-pointer outline-none" name="grup_filter" id="grup_filter">
                        <option value="">Semua Grup</option>
                        @foreach ($grups as $grup)
                        <option value="{{ $grup->id }}" {{ request('grup_filter') == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Filter Mesin
                    </label>
                    <select class="w-full h-10 px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all cursor-pointer outline-none" name="mesin_filter" id="mesin_filter">
                        <option value="">Semua Mesin</option>
                        @foreach ($mesins as $mesin)
                        <option value="{{ $mesin->id }}" {{ request('mesin_filter') == $mesin->id ? 'selected' : '' }}>
                            {{ $mesin->nama_mesin }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap pt-3.5 border-t border-slate-100 dark:border-slate-800/60">
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 active:bg-rose-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-rose-500/20 transition-all cursor-pointer">
                    <i class="fas fa-filter text-[11px]"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('laporan-produksi.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <i class="fas fa-redo text-[11px]"></i>
                    <span>Reset Filter</span>
                </a>

                @if(request('tanggal_awal') && request('tanggal_akhir'))
                <a href="{{ route('laporan-produksi.cetak', [
                            'tanggal_awal' => request('tanggal_awal'),
                            'tanggal_akhir' => request('tanggal_akhir'),
                            'grup_filter' => request('grup_filter'),
                            'mesin_filter' => request('mesin_filter')
                        ]) }}"
                    class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white flex items-center justify-center shadow-sm shadow-emerald-500/20 transition-all sm:ml-auto cursor-pointer"
                    target="_blank"
                    title="Cetak PDF Hasil Filter"
                    data-toggle="tooltip">
                    <i class="fas fa-file-pdf text-sm"></i>
                </a>
                @else
                <a href="{{ route('laporan-produksi.cetak', [
                            'grup_filter' => request('grup_filter'),
                            'mesin_filter' => request('mesin_filter')
                        ]) }}"
                    class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white flex items-center justify-center shadow-sm shadow-emerald-500/20 transition-all sm:ml-auto cursor-pointer"
                    target="_blank"
                    title="Cetak Semua Laporan"
                    data-toggle="tooltip">
                    <i class="fas fa-file-pdf text-sm"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-table"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight mb-0">
                        Data Laporan Produksi
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                        Daftar batch laporan produksi yang telah berstatus approved
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Total: {{ $dataProduksi->count() }} Data
            </span>
        </div>

        <div class="p-4 sm:p-5">
            <table id="tableLaporanProduksi"
                class="w-full text-left"
                style="width:100%"
                data-leader-inject="{{ $isLeaderInject ? 'true' : 'false' }}"
                data-leader-assy="{{ $isLeaderAssy ? 'true' : 'false' }}"
                data-show-url="{{ route('laporan-produksi.show', ':id') }}"
                data-detail-url="{{ route('laporan-produksi.get-detail', ':id') }}"
                data-cetak-url="{{ route('laporan-produksi.cetak-single', ':id') }}">
                <thead>
                    <tr>
                        <th class="text-center w-12">No</th>
                        <th class="whitespace-nowrap min-w-[130px]">Tanggal & Waktu</th>
                        <th class="text-center min-w-[90px]">Bagian</th>
                        <th class="min-w-[150px]">Mesin / Meja</th>
                        <th class="min-w-[140px]">Operator</th>
                        <th class="text-right whitespace-nowrap min-w-[90px]">Qty Plan</th>
                        <th class="text-right whitespace-nowrap min-w-[90px]">OK Count</th>
                        <th class="text-right whitespace-nowrap min-w-[85px]">NG Count</th>
                        <th class="text-right whitespace-nowrap min-w-[95px]">Total Output</th>
                        <th class="text-center whitespace-nowrap w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataProduksi as $index => $data)
                    <tr data-bagian="{{ $data->planning->bagian ?? 'inject' }}">
                        <td class="text-center font-bold text-slate-500 dark:text-slate-400 text-xs">
                            {{ $index + 1 }}
                        </td>
                        <td class="text-xs font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap" data-order="{{ $data->created_at ? $data->created_at->timestamp : 0 }}">
                            <div class="flex items-center gap-1.5">
                                <i class="far fa-calendar-alt text-slate-400 text-[11px]"></i>
                                <span>{{ $data->created_at ? $data->created_at->format('d-m-Y H:i') : '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center" data-search="{{ ($data->planning->bagian ?? 'inject') === 'assy' ? 'assy assembly' : 'inject' }}">
                            @if(($data->planning->bagian ?? 'inject') === 'assy')
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                                Assembly
                            </span>
                            @else
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60">
                                Inject
                            </span>
                            @endif
                        </td>
                        <td class="font-bold text-xs text-slate-800 dark:text-slate-100">
                            <div class="flex items-center gap-1.5">
                                <i class="fas {{ ($data->planning->bagian ?? 'inject') === 'assy' ? 'fa-tools text-indigo-500' : 'fa-cogs text-blue-500' }} text-[11px]"></i>
                                <span>
                                    @if(($data->planning->bagian ?? 'inject') === 'assy')
                                    {{ $data->planning->meja->nama_meja ?? '-' }}
                                    @else
                                    {{ $data->planning->mesin->nama_mesin ?? '-' }}
                                    @endif
                                </span>
                            </div>
                            @if($data->planning && $data->planning->part)
                            <div class="text-[11px] font-normal text-slate-400 dark:text-slate-500 truncate max-w-[160px] mt-0.5" title="{{ $data->planning->part->nama_part }}">
                                {{ $data->planning->part->nama_part }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($data->planning && $data->planning->plotings && $data->planning->plotings->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($data->planning->plotings as $ploting)
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                    {{ $ploting->operator->nama ?? '-' }}
                                </span>
                                @endforeach
                            </div>
                            @elseif($data->ploting && $data->ploting->operator)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                {{ $data->ploting->operator->nama }}
                            </span>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold text-xs text-slate-700 dark:text-slate-300" data-order="{{ $data->planning->qty_planning_produksi ?? 0 }}">
                            {{ number_format($data->planning->qty_planning_produksi ?? 0) }}
                        </td>
                        <td class="text-right" data-order="{{ $data->ok_count }}">
                            <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60">
                                {{ number_format($data->ok_count) }}
                            </span>
                        </td>
                        <td class="text-right" data-order="{{ $data->ng_count }}">
                            @if($data->ng_count > 0)
                            <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-lg bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60">
                                {{ number_format($data->ng_count) }}
                            </span>
                            @else
                            <span class="text-slate-400 font-mono text-xs font-semibold">0</span>
                            @endif
                        </td>
                        <td class="text-right font-mono font-black text-xs text-slate-900 dark:text-white" data-order="{{ $data->ok_count + $data->ng_count }}">
                            {{ number_format($data->ok_count + $data->ng_count) }}
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Quick Preview Modal Button --}}
                                <button type="button"
                                    class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60 text-xs transition-all btn-view-detail cursor-pointer"
                                    data-id="{{ $data->id }}"
                                    title="Preview Singkat"
                                    data-toggle="tooltip">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </button>

                                {{-- Full Page View Button --}}
                                <a href="{{ route('laporan-produksi.show', $data->id) }}"
                                    class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 text-xs transition-all cursor-pointer"
                                    title="Buka Laporan Lengkap"
                                    data-toggle="tooltip">
                                    <i class="fas fa-external-link-alt text-[11px]"></i>
                                </a>

                                {{-- Print PDF Button --}}
                                <a href="{{ route('laporan-produksi.cetak-single', $data->id) }}"
                                    target="_blank"
                                    class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 text-xs transition-all cursor-pointer"
                                    title="Cetak PDF Dokumen"
                                    data-toggle="tooltip">
                                    <i class="fas fa-print text-[11px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail Modal with Modern Tailwind UI --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
            <div class="modal-header px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0" id="detailModalLabel">
                            Preview Laporan Produksi
                        </h5>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">
                            Ringkasan informasi batch produksi yang telah diapprove
                        </p>
                    </div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-5 text-xs text-slate-700 dark:text-slate-300" id="detailModalBody">
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-10 h-10 border-4 border-rose-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer px-5 py-3 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between gap-2 flex-wrap">
                <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-all cursor-pointer" data-dismiss="modal">
                    Tutup
                </button>
                <div class="flex items-center gap-2">
                    <a href="#" id="btnShowFullModal" class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-800/60 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-external-link-alt text-[11px]"></i>
                        <span>Buka Halaman Lengkap</span>
                    </a>
                    <a href="#" id="btnCetakModal" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-500/20 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-print text-[11px]"></i>
                        <span>Cetak Dokumen PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
{{-- Custom JS --}}
<script src="{{ asset('js/laporan-produksi-index.js') }}?v={{ filemtime(public_path('js/laporan-produksi-index.js')) }}"></script>
@endpush