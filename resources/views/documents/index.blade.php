@extends('layouts.app')

@section('title', 'Pengaturan Dokumen Mesin & Meja - Digital Production System')

@push('styles')
<!-- Custom Admin Documents CSS -->
<link rel="stylesheet" href="{{ asset('css/documents.css') }}?v={{ filemtime(public_path('css/documents.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-file-contract"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Pengaturan Dokumen Mesin & Meja
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $mesins->count() }} Mesin</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    <span>{{ $mejas->count() }} Meja</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Standar Operasional Kerja: APD, Q-Point, Instruksi Kerja, Limit Sampel, dan AMTPM
            </p>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Statistics KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="kpiCardsContainer">
        {{-- Total Mesin --}}
        <div class="kpi-card kpi-card-indigo group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer select-none active"
            data-filter="all-mesin"
            title="Klik untuk menampilkan semua mesin">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-indigo-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    Total Mesin
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-slate-900 dark:text-white group-hover:scale-105 transition-transform origin-left inline-block kpi-counter" data-target="{{ $mesins->count() }}">
                        {{ number_format($mesins->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Unit</span>
                </div>
            </div>
            <div class="kpi-icon w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-600 group-hover:shadow-md group-hover:shadow-indigo-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 dark:bg-indigo-500"></div>
        </div>

        {{-- Mesin Running --}}
        <div class="kpi-card kpi-card-emerald group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer select-none"
            data-filter="running"
            title="Klik untuk filter mesin berstatus Running">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-emerald-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    Mesin Running
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform origin-left inline-block kpi-counter" data-target="{{ $mesins->where('status', 'running')->count() }}">
                        {{ number_format($mesins->where('status', 'running')->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Siap Upload</span>
                </div>
            </div>
            <div class="kpi-icon w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-emerald-500 group-hover:text-white dark:group-hover:bg-emerald-500 group-hover:shadow-md group-hover:shadow-emerald-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 hidden"></div>
        </div>

        {{-- Mesin Non-Aktif / Stand By --}}
        <div class="kpi-card kpi-card-amber group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer select-none"
            data-filter="standby-breakdown"
            title="Klik untuk filter mesin berstatus Stand By / Breakdown / OFF">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-amber-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                    Stand By / Breakdown
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-amber-500 dark:text-amber-400 group-hover:scale-105 transition-transform origin-left inline-block kpi-counter" data-target="{{ $mesins->where('status', '!=', 'running')->count() }}">
                        {{ number_format($mesins->where('status', '!=', 'running')->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Terkunci</span>
                </div>
            </div>
            <div class="kpi-icon kpi-icon-reverse w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white dark:group-hover:bg-amber-500 group-hover:shadow-md group-hover:shadow-amber-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-amber-500 hidden"></div>
        </div>

        {{-- Total Meja --}}
        <div class="kpi-card kpi-card-sky group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer select-none"
            data-filter="all-meja"
            title="Klik untuk membuka tab Per Meja">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-sky-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                    Total Meja
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 group-hover:scale-105 transition-transform origin-left inline-block kpi-counter" data-target="{{ $mejas->count() }}">
                        {{ number_format($mejas->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Stasiun</span>
                </div>
            </div>
            <div class="kpi-icon kpi-icon-reverse w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-sky-500 group-hover:text-white dark:group-hover:bg-sky-500 group-hover:shadow-md group-hover:shadow-sky-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-table"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-sky-500 hidden"></div>
        </div>
    </div>

    {{-- Session Alerts --}}
    @if (session('success'))
    <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm shadow-sm transition-all" role="alert">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-xs"></i>
        </div>
        <div class="flex-1 font-medium">
            {{ session('success') }}
        </div>
        <button type="button" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm shadow-sm transition-all" role="alert">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-xs"></i>
        </div>
        <div class="flex-1 font-medium">
            {{ session('error') }}
        </div>
        <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if (isset($errors) && $errors->any())
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm shadow-sm transition-all" role="alert">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-ban text-xs"></i>
        </div>
        <div class="flex-1 font-medium">
            <ul class="mb-0 pl-3 list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Main Document Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Daftar Dokumen Standar</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Standar mutu, keamanan, dan operasional kerja</p>
                </div>
            </div>

            {{-- Tabs Selector --}}
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700/60" role="tablist">
                <a class="nav-link active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer" data-toggle="tab" href="#tab-mesin" role="tab">
                    <i class="fas fa-cogs text-xs"></i>
                    <span>Per Mesin ({{ $mesins->count() }})</span>
                </a>
                <a class="nav-link inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer" data-toggle="tab" href="#tab-meja" role="tab">
                    <i class="fas fa-table text-xs"></i>
                    <span>Per Meja ({{ $mejas->count() }})</span>
                </a>
            </div>
        </div>

        @php
        $types = [
            'apd' => ['label' => 'APD', 'icon' => 'fa-shield-alt', 'color' => 'primary'],
            'qpoint' => ['label' => 'Q-Point', 'icon' => 'fa-award', 'color' => 'success'],
            'instruksi_kerja' => ['label' => 'Instruksi Kerja', 'icon' => 'fa-file-alt', 'color' => 'info'],
            'limit_sampel' => ['label' => 'Limit Sampel', 'icon' => 'fa-chart-bar', 'color' => 'warning'],
            'amptm' => ['label' => 'AMTPM', 'icon' => 'fa-clipboard-list', 'color' => 'danger']
        ];
        @endphp

        <div class="p-4 sm:p-5">
            <div class="tab-content">
                {{-- ======================== TAB MESIN ======================== --}}
                <div class="tab-pane fade show active" id="tab-mesin" role="tabpanel">
                    <table id="mesin-table" class="doc-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 220px; min-width: 220px;">
                                    <i class="fas fa-cogs mr-1"></i> Nama Mesin
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-shield-alt mr-1"></i> APD
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-award mr-1"></i> Q-Point
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-file-alt mr-1"></i> IK
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-chart-bar mr-1"></i> Limit Sampel
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-clipboard-list mr-1"></i> AMTPM
                                </th>
                                <th style="width: 270px; min-width: 270px;" class="text-center">
                                    <i class="fas fa-upload mr-1"></i> Upload Dokumen
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mesins as $mesin)
                            @php
                            $isDisabled = ($mesin->status !== 'running');
                            $statusMessage = '';
                            if ($mesin->status === 'maintenance') {
                                $statusMessage = 'Sedang Breakdown / Perbaikan';
                            } elseif ($mesin->status === 'offline') {
                                $statusMessage = 'Mesin OFF / Tidak Ada Planning';
                            } elseif ($mesin->status === 'stop') {
                                $statusMessage = 'Mesin Stand By';
                            }

                            $upcomingParts = collect();
                            if (isset($mesin->plannings) && $mesin->plannings) {
                                $upcomingParts = $mesin->plannings->filter(function($p) {
                                    return isset($p->tanggal) && $p->tanggal >= now()->format('Y-m-d');
                                })->take(5);
                            }
                            @endphp
                            <tr class="{{ $isDisabled ? 'machine-disabled' : '' }}" data-status="{{ $mesin->status }}">
                                <td>
                                    <div class="space-y-1.5">
                                        <div>
                                            <strong class="text-sm font-bold text-slate-900 dark:text-white block">{{ $mesin->nama_mesin }}</strong>
                                            @if($mesin->brand_mesin)
                                            <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ $mesin->brand_mesin }}</span>
                                            @endif
                                        </div>

                                        {{-- Machine Status Badge --}}
                                        <div>
                                            @if($mesin->status == 'running')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Running
                                            </span>
                                            @elseif($mesin->status == 'maintenance')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60" title="{{ $mesin->stop_reason }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Breakdown {{ $mesin->stop_reason ? '('. \Illuminate\Support\Str::limit($mesin->stop_reason, 15) .')' : '' }}
                                            </span>
                                            @elseif($mesin->status == 'offline')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                OFF
                                            </span>
                                            @elseif($mesin->status == 'stop')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Stand By
                                            </span>
                                            @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ ucfirst($mesin->status ?? '-') }}
                                            </span>
                                            @endif
                                        </div>

                                        @if($isDisabled)
                                        <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-[11px] flex items-start gap-1.5">
                                            <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400"></i>
                                            <span><strong>{{ $statusMessage }}</strong><br>Upload dokumen terkunci.</span>
                                        </div>
                                        @endif

                                        {{-- Informasi Part yang Akan Dikerjakan --}}
                                        @if($upcomingParts && $upcomingParts->count() > 0)
                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">
                                                <i class="fas fa-clipboard-list mr-1"></i>Part Mendatang
                                            </span>
                                            <div class="space-y-1.5 max-h-28 overflow-y-auto custom-scrollbar pr-1">
                                                @foreach($upcomingParts as $planning)
                                                @if($planning->part)
                                                <div class="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800 text-[11px]">
                                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 block">
                                                        {{ $planning->part->no_parts ?? '-' }}
                                                    </span>
                                                    <span class="text-slate-700 dark:text-slate-300 font-medium block truncate" title="{{ $planning->part->nama_part }}">
                                                        {{ $planning->part->nama_part ?? '-' }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block">
                                                        <i class="fas fa-calendar-alt mr-1"></i>{{ isset($planning->tanggal) ? \Carbon\Carbon::parse($planning->tanggal)->format('d/m/Y') : '-' }}
                                                        @if(isset($planning->qty_planning_produksi)) | Qty: {{ number_format($planning->qty_planning_produksi) }} @endif
                                                    </span>
                                                </div>
                                                @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </td>

                                @foreach ($types as $key => $typeInfo)
                                @php
                                $doc = null;
                                if (method_exists($mesin, 'getDocument')) {
                                    $doc = $mesin->getDocument($key);
                                }
                                @endphp
                                <td class="text-center align-middle">
                                    @if ($doc)
                                    <div class="space-y-1.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 whitespace-nowrap">
                                            <i class="fas fa-check-circle text-emerald-500 text-xs"></i> Ada
                                        </span>
                                        @if($doc->filename)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono block truncate max-w-[110px] mx-auto" title="{{ $doc->filename }}">
                                            {{ $doc->filename }}
                                        </span>
                                        @endif
                                        <button type="button"
                                            class="btn-view-document inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 transition-all cursor-pointer shadow-sm"
                                            data-doc-url="{{ asset($doc->url) }}"
                                            data-doc-name="{{ $doc->filename ?? $typeInfo['label'] }}"
                                            title="Lihat Dokumen: {{ $doc->filename ?? 'Dokumen' }}">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat</span>
                                        </button>
                                    </div>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/40 whitespace-nowrap">
                                        <i class="fas fa-times-circle text-xs"></i> Tidak Ada
                                    </span>
                                    @endif
                                </td>
                                @endforeach

                                <td class="align-middle">
                                    @if($isDisabled)
                                    <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-900/50 text-center space-y-1">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-sm">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300 block">Upload Terkunci</span>
                                        <span class="text-[11px] text-rose-600/80 dark:text-rose-400/80 block leading-tight">
                                            Hanya diizinkan saat status <strong>RUNNING</strong>
                                        </span>
                                    </div>
                                    @else
                                    <form action="{{ route('documents.upload.mesin', $mesin->id) }}"
                                        method="POST" enctype="multipart/form-data" class="upload-form space-y-2">
                                        @csrf
                                        <div>
                                            <select name="type" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none" required>
                                                <option value="">Pilih Jenis Dokumen</option>
                                                @foreach ($types as $key => $typeInfo)
                                                <option value="{{ $key }}">{{ $typeInfo['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" name="dokumen"
                                                class="custom-file-input"
                                                id="file-mesin-{{ $mesin->id }}"
                                                accept=".pdf,.doc,.docx"
                                                required>
                                            <label class="custom-file-label" for="file-mesin-{{ $mesin->id }}">
                                                Pilih file...
                                            </label>
                                        </div>
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer">
                                            <i class="fas fa-upload text-xs"></i>
                                            <span>Upload Dokumen</span>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ======================== TAB MEJA ======================== --}}
                <div class="tab-pane fade" id="tab-meja" role="tabpanel">
                    <table id="meja-table" class="doc-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 220px; min-width: 220px;">
                                    <i class="fas fa-table mr-1"></i> Nama Meja
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-shield-alt mr-1"></i> APD
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-award mr-1"></i> Q-Point
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-file-alt mr-1"></i> IK
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-chart-bar mr-1"></i> Limit Sampel
                                </th>
                                <th style="width: 110px; min-width: 110px;" class="text-center">
                                    <i class="fas fa-clipboard-list mr-1"></i> AMTPM
                                </th>
                                <th style="width: 270px; min-width: 270px;" class="text-center">
                                    <i class="fas fa-upload mr-1"></i> Upload Dokumen
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mejas as $meja)
                            @php
                            $isMejaDisabled = $meja->status === 'tidak_aktif';
                            @endphp
                            <tr class="{{ $isMejaDisabled ? 'machine-disabled' : '' }}" data-status="{{ $meja->status }}">
                                <td>
                                    <div class="space-y-1.5">
                                        <div>
                                            <strong class="text-sm font-bold text-slate-900 dark:text-white block">{{ $meja->nama_meja }}</strong>
                                            @if($meja->lokasi)
                                            <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ $meja->lokasi }}</span>
                                            @endif
                                        </div>

                                        <div>
                                            @if($meja->status === 'aktif')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                                <i class="fas fa-check-circle text-xs"></i>
                                                Aktif
                                            </span>
                                            @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                                                <i class="fas fa-ban text-xs"></i>
                                                Tidak Aktif
                                            </span>
                                            @endif
                                        </div>

                                        @if($isMejaDisabled)
                                        <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-[11px] flex items-center gap-1.5">
                                            <i class="fas fa-exclamation-triangle flex-shrink-0 text-amber-600 dark:text-amber-400"></i>
                                            <span><strong>Meja Tidak Aktif</strong> - Upload terkunci.</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>

                                @foreach ($types as $key => $typeInfo)
                                @php
                                $doc = null;
                                if (method_exists($meja, 'getDocument')) {
                                    $doc = $meja->getDocument($key);
                                }
                                @endphp
                                <td class="text-center align-middle">
                                    @if ($doc)
                                    <div class="space-y-1.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 whitespace-nowrap">
                                            <i class="fas fa-check-circle text-emerald-500 text-xs"></i> Ada
                                        </span>
                                        @if($doc->filename)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono block truncate max-w-[110px] mx-auto" title="{{ $doc->filename }}">
                                            {{ $doc->filename }}
                                        </span>
                                        @endif
                                        <button type="button"
                                            class="btn-view-document inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 transition-all cursor-pointer shadow-sm"
                                            data-doc-url="{{ asset($doc->url) }}"
                                            data-doc-name="{{ $doc->filename ?? $typeInfo['label'] }}"
                                            title="Lihat Dokumen: {{ $doc->filename ?? 'Dokumen' }}">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat</span>
                                        </button>
                                    </div>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/40 whitespace-nowrap">
                                        <i class="fas fa-times-circle text-xs"></i> Tidak Ada
                                    </span>
                                    @endif
                                </td>
                                @endforeach

                                <td class="align-middle">
                                    @if($isMejaDisabled)
                                    <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-900/50 text-center space-y-1">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-sm">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300 block">Upload Dibatalkan</span>
                                        <span class="text-[11px] text-rose-600/80 dark:text-rose-400/80 block leading-tight">
                                            Meja berstatus <strong>Tidak Aktif</strong>
                                        </span>
                                    </div>
                                    @else
                                    <form action="{{ route('documents.upload.meja', $meja->id_meja) }}"
                                        method="POST" enctype="multipart/form-data" class="upload-form space-y-2">
                                        @csrf
                                        <div>
                                            <select name="type" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none" required>
                                                <option value="">Pilih Jenis Dokumen</option>
                                                @foreach ($types as $key => $typeInfo)
                                                <option value="{{ $key }}">{{ $typeInfo['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" name="dokumen"
                                                class="custom-file-input"
                                                id="file-meja-{{ $meja->id_meja }}"
                                                accept=".pdf,.doc,.docx"
                                                required>
                                            <label class="custom-file-label" for="file-meja-{{ $meja->id_meja }}">
                                                Pilih file...
                                            </label>
                                        </div>
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer">
                                            <i class="fas fa-upload text-xs"></i>
                                            <span>Upload Dokumen</span>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Document Viewer -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" role="dialog" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="documentViewerModalLabel">
                            <span class="modal-title-text">Dokumen Standar</span>
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Pratinjau berkas dokumen standar produksi
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-0 bg-slate-900 h-[70vh]">
                <iframe id="documentViewer" src="" class="w-full h-full border-0"></iframe>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
                <a href="#" class="btn-download-doc inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer" target="_blank">
                    <i class="fas fa-download text-xs"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Custom Documents JS -->
<script src="{{ asset('js/documents.js') }}?v={{ filemtime(public_path('js/documents.js')) }}"></script>
@endpush