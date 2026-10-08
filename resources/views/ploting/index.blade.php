@extends('layouts.app')

@section('title', 'Plooting Produksi - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Ploting CSS -->
<link rel="stylesheet" href="{{ asset('css/ploting.css') }}?v={{ filemtime(public_path('css/ploting.css')) }}">
<!-- Planning CSS for Tab Filter & Detail Modal -->
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-lg shadow-md shadow-blue-500/20 flex-shrink-0">
            <i class="fas fa-tasks"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                Plooting Produksi
            </h1>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                Penugasan operator mesin & meja produksi serta monitoring real-time
            </p>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Filter Tanggal dan Shift --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm transition-all duration-200 filter-card">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold">
                    <i class="fas fa-filter text-[10px]"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider mb-0">
                    Filter Data Produksi
                </h3>
            </div>
            @if(request('tanggal'))
            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1.5" id="filterStatus">
                <i class="fas fa-check-circle text-[10px]"></i>Filter Aktif
            </span>
            @else
            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1.5" id="filterStatus" style="display: none;">
                <i class="fas fa-check-circle text-[10px]"></i>Filter Aktif
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            {{-- Filter Tanggal --}}
            <div class="flex flex-col">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Tanggal Produksi
                </label>
                <div class="flex items-center gap-2 h-10">
                    <select class="flex-1 min-w-0 h-full px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer" id="filterTanggalMode">
                        <option value="today">Hari Ini ({{ date('d-m-Y') }})</option>
                        <option value="tomorrow">Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                        <option value="custom">Custom</option>
                    </select>
                    <input type="date" class="flex-1 min-w-0 h-full px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" id="filterTanggal" value="{{ request('tanggal', date('Y-m-d')) }}" style="display:none;">
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1 mb-0">
                    <i class="fas fa-calendar-day text-[10px]"></i>
                    <span>Pilih tanggal kerja produksi</span>
                </p>
            </div>

            {{-- Filter Shift --}}
            <div class="flex flex-col">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Filter Shift (Opsional)
                </label>
                <div class="h-10">
                    <select class="w-full h-full px-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer" id="filterShift">
                        <option value="">Semua Shift</option>
                        <option value="pagi">Shift Pagi</option>
                        <option value="siang">Shift Siang</option>
                        <option value="malam">Shift Malam</option>
                    </select>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1 mb-0">
                    <i class="fas fa-info-circle text-[10px]"></i>
                    <span>Filter operator dari jadwal kerja</span>
                </p>
            </div>

            {{-- Tombol Terapkan Filter --}}
            <div class="flex flex-col">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 select-none md:invisible hidden md:block">
                    Aksi
                </label>
                <div class="h-10">
                    <form method="GET" action="{{ route('ploting.index') }}" class="w-full h-full flex items-center" id="plotingFilterForm">
                        <input type="hidden" name="tanggal" id="tanggalParam" value="{{ request('tanggal', date('Y-m-d')) }}">
                        <button class="w-full h-full px-4 rounded-xl bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold text-xs inline-flex items-center justify-center gap-2 shadow-sm shadow-blue-500/20 transition-all duration-200 cursor-pointer" id="applyFilter" type="submit">
                            <i class="fas fa-filter text-[11px]"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </form>
                </div>
                <p class="text-[11px] text-transparent select-none mt-1.5 hidden md:block mb-0">
                    &nbsp;
                </p>
            </div>
        </div>

        {{-- Info Box H-1 --}}
        <div class="mt-4 p-3.5 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-900/50 rounded-xl text-xs text-blue-900 dark:text-blue-200">
            <div class="flex items-start gap-2.5">
                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0 text-sm"></i>
                <div class="leading-relaxed">
                    <span class="font-bold">Ploting H-1:</span> Sistem dirancang untuk ploting hari sebelumnya (H-1). Saat Anda memilih grup, operator otomatis dimuat sesuai jadwal kerjanya pada tanggal yang dipilih.
                    <div class="mt-1 text-slate-500 dark:text-slate-400 text-[11px]">
                        <strong>Tips:</strong> Untuk ploting besok, pilih tanggal besok pada opsi Tanggal Produksi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-xl p-4 flex items-center justify-between shadow-sm" role="alert">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-base"></i>
            <span class="text-xs font-semibold">{{ session('success') }}</span>
        </div>
        <button type="button" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-xl p-4 flex items-start justify-between shadow-sm" role="alert">
        <div class="flex items-start gap-2.5">
            <i class="fas fa-exclamation-triangle text-rose-600 dark:text-rose-400 text-base mt-0.5"></i>
            <div>
                <span class="text-xs font-bold block mb-1">Terjadi kesalahan:</span>
                <ul class="text-xs list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Tab Filter Bagian (Hanya jika bukan leader spesifik) --}}
    @if((!isset($isLeaderInject) || !$isLeaderInject) && (!isset($isLeaderAssy) || !$isLeaderAssy))
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-1.5 shadow-sm inline-flex flex-wrap items-center gap-1">
        <ul class="nav nav-pills flex items-center gap-1 m-0 p-0 list-none" id="bagianTabsPloting" role="tablist">
            <li class="nav-item">
                <a class="nav-link active px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="all-tab-ploting" href="#" data-bagian="all">
                    <i class="fas fa-list text-[11px]"></i>
                    <span>Semua Bagian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="inject-tab-ploting" href="#" data-bagian="inject">
                    <i class="fas fa-cog text-[11px]"></i>
                    <span>Inject</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 cursor-pointer" id="assy-tab-ploting" href="#" data-bagian="assy">
                    <i class="fas fa-tools text-[11px]"></i>
                    <span>Assembly</span>
                </a>
            </li>
        </ul>
    </div>
    @endif

    {{-- Table Section untuk INJECT --}}
    @if($planningsInject->count() > 0)
    <div class="ploting-section space-y-3" id="inject-section" @if(isset($isLeaderInject) && $isLeaderInject) style="display: block;" @elseif(isset($isLeaderAssy) && $isLeaderAssy) style="display: none;" @endif>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden table-card">
            {{-- Header Card --}}
            <div class="px-5 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight mb-0">
                            Daftar Plooting Produksi - INJECT
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                            {{ $planningsInject->count() }} Perencanaan Produksi Bagian Inject
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60">
                    INJECT
                </span>
            </div>

            {{-- Table Body --}}
            <div class="p-4 sm:p-5">
                <div class="overflow-x-auto">
                    <table id="plotingTableInject" class="table table-bordered align-middle plotingTable w-full" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:120px;" class="text-center">Tanggal</th>
                                <th>Part</th>
                                <th>Mesin</th>
                                <th class="text-center" style="width:100px;">Qty</th>
                                <th style="min-width:620px;">Detail Plooting</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planningsInject as $planning)
                            <tr>
                                <td class="text-center align-top">
                                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ optional($planning->tanggal)->format('d/m/Y') }}</div>
                                    <small class="text-slate-400 dark:text-slate-500 text-[11px]">{{ optional($planning->tanggal)->isoFormat('dddd') }}</small>
                                </td>
                                <td class="align-top">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $planning->part->nama_part ?? '-' }}</div>
                                    <button type="button" class="mt-1.5 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60 text-[11px] font-semibold inline-flex items-center gap-1.5 transition-all showBtn cursor-pointer shadow-xs"
                                        title="Lihat Detail Planning"
                                        data-id="{{ $planning->id }}"
                                        data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                        data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                        data-tanggal="{{ optional($planning->tanggal)->format('d-m-Y') }}"
                                        data-lot="{{ $planning->lot_produksi }}"
                                        data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                        data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                        data-type="{{ $planning->part->type ?? '-' }}"
                                        data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                        data-status="{{ $planning->status }}"
                                        data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => ['shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                        data-operators='@json($planning->plotings->map(fn($p) => [
                                            'nama' => $p->operator->nama ?? '-',
                                            'grup' => $p->grup->nama_grup ?? '-',
                                            'status' => $p->status ?? 'Belum di-assign'
                                        ]))'>
                                        <i class="fas fa-eye text-[10px]"></i>
                                        <span>Detail</span>
                                    </button>
                                </td>
                                <td class="align-top font-semibold text-slate-700 dark:text-slate-200 text-xs">
                                    {{ $planning->mesin->nama_mesin ?? '-' }}
                                </td>
                                <td class="text-center align-top">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60">
                                        {{ number_format($planning->qty_planning_produksi ?? 0) }}
                                    </span>
                                </td>
                                <td class="p-2">
                                    {{-- Nested Table untuk Detail Operator --}}
                                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                        <table class="table table-sm mb-0 nested-operator-table w-full">
                                            <thead>
                                                <tr>
                                                    <th style="width:100px;" class="text-center">Grup</th>
                                                    <th style="min-width:260px;">Operator</th>
                                                    <th class="text-center" style="width:100px;">Shift</th>
                                                    <th class="text-center" style="width:120px;">Status</th>
                                                    <th class="text-center" style="width:120px;">Run Time</th>
                                                    <th class="text-center" style="width:100px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($planning->plotings as $ploting)
                                                <tr>
                                                    {{-- Kolom Grup --}}
                                                    <td class="text-center align-middle">
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                            {{ $ploting->grup->nama_grup ?? '-' }}
                                                        </span>
                                                    </td>

                                                    {{-- Kolom Operator --}}
                                                    <td class="align-middle">
                                                        @if (!$ploting->user_id)
                                                        {{-- Belum ada operator --}}
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="flex items-center gap-1.5 add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="inject">

                                                            <select name="grup_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="p-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all add-operator-btn cursor-pointer" title="Tambah Operator">
                                                                <i class="fas fa-user-plus text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        @elseif (in_array($ploting->status, [null, 'waiting']))
                                                        {{-- Bisa update --}}
                                                        <form action="{{ route('ploting.update', $ploting->id) }}"
                                                            method="POST"
                                                            class="flex items-center gap-1.5 update-form"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" class="bagian-input" value="inject">

                                                            <select name="grup_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 100px;" required>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}"
                                                                    {{ $ploting->grup_id == $grup->id ? 'selected' : '' }}>
                                                                    {{ $grup->nama_grup }}
                                                                </option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 120px;"
                                                                data-current-user="{{ $ploting->user_id }}" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="p-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold transition-all update-btn cursor-pointer" title="Update Operator">
                                                                <i class="fas fa-save text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        @else
                                                        <div class="flex items-center justify-center gap-1.5">
                                                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $ploting->operator->nama ?? '-' }}</span>
                                                            <i class="fas fa-lock text-slate-400 text-xs" data-toggle="tooltip"
                                                                title="Tidak dapat diubah karena produksi sudah {{ $ploting->status == 'on_progress' ? 'dimulai' : 'selesai' }}"></i>
                                                        </div>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Shift --}}
                                                    <td class="text-center align-middle">
                                                        @php
                                                        $operatorShift = null;
                                                        if ($ploting->operator) {
                                                            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                            $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                                ->whereDate('tanggal', $tanggalPlotting)
                                                                ->where('status', 'aktif')
                                                                ->first();
                                                            if ($jadwal) {
                                                                $operatorShift = $jadwal->shift;
                                                            }
                                                        }
                                                        @endphp
                                                        @if ($operatorShift && $operatorShift !== 'off')
                                                        @php
                                                        $shiftClasses = [
                                                            'pagi' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60',
                                                            'siang' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                                                            'malam' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60',
                                                        ];
                                                        @endphp
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md {{ $shiftClasses[$operatorShift] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                                            Shift {{ ucfirst($operatorShift) }}
                                                        </span>
                                                        @else
                                                        <span class="text-slate-400 text-xs">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Status --}}
                                                    <td class="text-center align-middle">
                                                        @switch($ploting->status)
                                                        @case('waiting')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-clock text-[10px]"></i>Waiting
                                                        </span>
                                                        @break
                                                        @case('on_progress')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 inline-flex items-center gap-1 animate-pulse">
                                                            <i class="fas fa-play text-[10px]"></i>On Progress
                                                        </span>
                                                        @break
                                                        @case('done')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-check text-[10px]"></i>Done
                                                        </span>
                                                        @break
                                                        @default
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 inline-flex items-center gap-1">
                                                            <i class="fas fa-user-slash text-[10px]"></i>Belum di-assign
                                                        </span>
                                                        @endswitch
                                                    </td>

                                                    {{-- Kolom Run Time --}}
                                                    <td class="text-center align-middle">
                                                        @if ($ploting->status === 'done' && $ploting->run_time)
                                                        <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-hourglass-end text-[10px]"></i>
                                                            {{ gmdate('H:i:s', $ploting->run_time) }}
                                                        </span>
                                                        @elseif ($ploting->status === 'on_progress')
                                                        @php
                                                        $shiftEndTime = null;
                                                        if ($ploting->operator) {
                                                            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                            $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                                ->whereDate('tanggal', $tanggalPlotting)
                                                                ->where('status', 'aktif')
                                                                ->first();

                                                            if ($jadwal && $jadwal->jam_keluar) {
                                                                try {
                                                                    $jamKeluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                                                                    $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                                                                } catch (\Exception $e) {
                                                                    $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                                                                    $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                                                                }

                                                                if ($jamKeluar->lessThan($jamMasuk)) {
                                                                    $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                                                                } else {
                                                                    $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                                                                }
                                                            }
                                                        }
                                                        @endphp
                                                        <span class="badge badge-info px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 inline-flex items-center gap-1" id="runtime-{{ $ploting->id }}"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-start-time="{{ $ploting->start_time }}"
                                                            data-shift-end-time="{{ $shiftEndTime ? $shiftEndTime->format('Y-m-d H:i:s') : '' }}">
                                                            <i class="fas fa-spinner fa-spin text-[10px]"></i>
                                                            <span class="runtime-display">Berjalan...</span>
                                                        </span>
                                                        @else
                                                        <span class="text-slate-400 text-xs">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Aksi --}}
                                                    <td class="text-center align-middle">
                                                        @if ($ploting->user_id)
                                                        @if ($ploting->status === null || $ploting->status === 'waiting')
                                                        @php
                                                        $missingDocs = $planning->mesin ? $planning->mesin->getMissingDocuments() : ['Semua Dokumen'];
                                                        $isDocComplete = $planning->mesin && count($missingDocs) === 0;
                                                        @endphp
                                                        <button type="button" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm shadow-blue-500/20 transition-all start-btn cursor-pointer"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            data-doc-complete="{{ $isDocComplete ? 'true' : 'false' }}"
                                                            data-missing-docs="{{ implode(', ', $missingDocs) }}"
                                                            title="Start Produksi">
                                                            <i class="fas fa-play text-[10px]"></i>
                                                            <span>Start</span>
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'on_progress')
                                                        <button type="button" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm shadow-emerald-500/20 transition-all finish-btn cursor-pointer"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            title="Finish Produksi">
                                                            <i class="fas fa-stop text-[10px]"></i>
                                                            <span>Finish</span>
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'done')
                                                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-check-circle text-[10px]"></i>Selesai
                                                        </span>
                                                        @endif
                                                        @else
                                                        <small class="text-slate-400 text-xs">
                                                            <i class="fas fa-info-circle mr-0.5"></i>Assign dulu
                                                        </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-slate-400 py-3 text-xs">
                                                        <i class="fas fa-users-slash mr-1.5"></i>Belum ada operator
                                                    </td>
                                                </tr>
                                                @endforelse

                                                {{-- Form tambah operator baru --}}
                                                <tr class="bg-slate-50/70 dark:bg-slate-800/50">
                                                    <td colspan="6" class="p-2">
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="flex items-center justify-center gap-2 add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="inject">

                                                            <select name="grup_id"
                                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Pilih Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 150px;" required>
                                                                <option value="">Pilih Operator</option>
                                                            </select>

                                                            <button type="button" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 transition-all add-operator-btn cursor-pointer" title="Tambah Operator">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                                <span>Tambah</span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-400 py-6 text-xs">
                                    <i class="fas fa-clipboard-list mr-1.5"></i>Tidak ada planning produksi Inject
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Table Section untuk ASSEMBLY --}}
    @if($planningsAssy->count() > 0)
    <div class="ploting-section space-y-3" id="assy-section" @if(isset($isLeaderInject) && $isLeaderInject) style="display: none;" @elseif(isset($isLeaderAssy) && $isLeaderAssy) style="display: block;" @endif>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden table-card">
            {{-- Header Card --}}
            <div class="px-5 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight mb-0">
                            Daftar Plooting Produksi - ASSEMBLY
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                            {{ $planningsAssy->count() }} Perencanaan Produksi Bagian Assembly
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60">
                    ASSEMBLY
                </span>
            </div>

            {{-- Table Body --}}
            <div class="p-4 sm:p-5">
                <div class="overflow-x-auto">
                    <table id="plotingTableAssy" class="table table-bordered align-middle plotingTable w-full" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:120px;" class="text-center">Tanggal</th>
                                <th>Part</th>
                                <th>Meja</th>
                                <th class="text-center" style="width:100px;">Qty</th>
                                <th style="min-width:620px;">Detail Plooting</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planningsAssy as $planning)
                            <tr>
                                <td class="text-center align-top">
                                    <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ optional($planning->tanggal)->format('d/m/Y') }}</div>
                                    <small class="text-slate-400 dark:text-slate-500 text-[11px]">{{ optional($planning->tanggal)->isoFormat('dddd') }}</small>
                                </td>
                                <td class="align-top">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $planning->part->nama_part ?? '-' }}</div>
                                    <button type="button" class="mt-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 text-[11px] font-semibold inline-flex items-center gap-1.5 transition-all showBtn cursor-pointer shadow-xs"
                                        title="Lihat Detail Planning"
                                        data-id="{{ $planning->id }}"
                                        data-bagian="assy"
                                        data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                        data-tanggal="{{ optional($planning->tanggal)->format('d-m-Y') }}"
                                        data-lot="{{ $planning->lot_produksi }}"
                                        data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                        data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                        data-type="{{ $planning->part->type ?? '-' }}"
                                        data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                        data-status="{{ $planning->status }}"
                                        data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => ['shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                        data-operators='@json($planning->plotings->map(fn($p) => [
                                            'nama' => $p->operator->nama ?? '-',
                                            'grup' => $p->grup->nama_grup ?? '-',
                                            'status' => $p->status ?? 'Belum di-assign'
                                        ]))'>
                                        <i class="fas fa-eye text-[10px]"></i>
                                        <span>Detail</span>
                                    </button>
                                </td>
                                <td class="align-top font-semibold text-slate-700 dark:text-slate-200 text-xs">
                                    {{ $planning->meja->nama_meja ?? '-' }}
                                </td>
                                <td class="text-center align-top">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60">
                                        {{ number_format($planning->qty_planning_produksi ?? 0) }}
                                    </span>
                                </td>
                                <td class="p-2">
                                    {{-- Nested Table untuk Detail Operator --}}
                                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                        <table class="table table-sm mb-0 nested-operator-table w-full">
                                            <thead>
                                                <tr>
                                                    <th style="width:100px;" class="text-center">Grup</th>
                                                    <th style="min-width:260px;">Operator</th>
                                                    <th class="text-center" style="width:100px;">Shift</th>
                                                    <th class="text-center" style="width:120px;">Status</th>
                                                    <th class="text-center" style="width:120px;">Run Time</th>
                                                    <th class="text-center" style="width:100px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($planning->plotings as $ploting)
                                                <tr>
                                                    {{-- Kolom Grup --}}
                                                    <td class="text-center align-middle">
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                            {{ $ploting->grup->nama_grup ?? '-' }}
                                                        </span>
                                                    </td>

                                                    {{-- Kolom Operator --}}
                                                    <td class="align-middle">
                                                        @if (!$ploting->user_id)
                                                        {{-- Belum ada operator --}}
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="flex items-center gap-1.5 add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="p-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all add-operator-btn cursor-pointer" title="Tambah Operator">
                                                                <i class="fas fa-user-plus text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        @elseif (in_array($ploting->status, [null, 'waiting']))
                                                        {{-- Bisa update --}}
                                                        <form action="{{ route('ploting.update', $ploting->id) }}"
                                                            method="POST"
                                                            class="flex items-center gap-1.5 update-form"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 100px;" required>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}"
                                                                    {{ $ploting->grup_id == $grup->id ? 'selected' : '' }}>
                                                                    {{ $grup->nama_grup }}
                                                                </option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 120px;"
                                                                data-current-user="{{ $ploting->user_id }}" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="p-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold transition-all update-btn cursor-pointer" title="Update Operator">
                                                                <i class="fas fa-save text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        @else
                                                        <div class="flex items-center justify-center gap-1.5">
                                                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $ploting->operator->nama ?? '-' }}</span>
                                                            <i class="fas fa-lock text-slate-400 text-xs" data-toggle="tooltip"
                                                                title="Tidak dapat diubah karena produksi sudah {{ $ploting->status == 'on_progress' ? 'dimulai' : 'selesai' }}"></i>
                                                        </div>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Shift --}}
                                                    <td class="text-center align-middle">
                                                        @php
                                                        $operatorShift = null;
                                                        if ($ploting->operator) {
                                                            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                            $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                                ->whereDate('tanggal', $tanggalPlotting)
                                                                ->where('status', 'aktif')
                                                                ->first();
                                                            if ($jadwal) {
                                                                $operatorShift = $jadwal->shift;
                                                            }
                                                        }
                                                        @endphp
                                                        @if ($operatorShift && $operatorShift !== 'off')
                                                        @php
                                                        $shiftClasses = [
                                                            'pagi' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60',
                                                            'siang' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                                                            'malam' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60',
                                                        ];
                                                        @endphp
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md {{ $shiftClasses[$operatorShift] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                                            Shift {{ ucfirst($operatorShift) }}
                                                        </span>
                                                        @else
                                                        <span class="text-slate-400 text-xs">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Status --}}
                                                    <td class="text-center align-middle">
                                                        @switch($ploting->status)
                                                        @case('waiting')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-clock text-[10px]"></i>Waiting
                                                        </span>
                                                        @break
                                                        @case('on_progress')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 inline-flex items-center gap-1 animate-pulse">
                                                            <i class="fas fa-play text-[10px]"></i>On Progress
                                                        </span>
                                                        @break
                                                        @case('done')
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-check text-[10px]"></i>Done
                                                        </span>
                                                        @break
                                                        @default
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 inline-flex items-center gap-1">
                                                            <i class="fas fa-user-slash text-[10px]"></i>Belum di-assign
                                                        </span>
                                                        @endswitch
                                                    </td>

                                                    {{-- Kolom Run Time --}}
                                                    <td class="text-center align-middle">
                                                        @if ($ploting->status === 'done' && $ploting->run_time)
                                                        <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-hourglass-end text-[10px]"></i>
                                                            {{ gmdate('H:i:s', $ploting->run_time) }}
                                                        </span>
                                                        @elseif ($ploting->status === 'on_progress')
                                                        @php
                                                        $shiftEndTime = null;
                                                        if ($ploting->operator) {
                                                            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                            $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                                ->whereDate('tanggal', $tanggalPlotting)
                                                                ->where('status', 'aktif')
                                                                ->first();

                                                            if ($jadwal && $jadwal->jam_keluar) {
                                                                try {
                                                                    $jamKeluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                                                                    $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                                                                } catch (\Exception $e) {
                                                                    $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                                                                    $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                                                                }

                                                                if ($jamKeluar->lessThan($jamMasuk)) {
                                                                    $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                                                                } else {
                                                                    $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                                                                }
                                                            }
                                                        }
                                                        @endphp
                                                        <span class="badge badge-info px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 inline-flex items-center gap-1" id="runtime-{{ $ploting->id }}"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-start-time="{{ $ploting->start_time }}"
                                                            data-shift-end-time="{{ $shiftEndTime ? $shiftEndTime->format('Y-m-d H:i:s') : '' }}">
                                                            <i class="fas fa-spinner fa-spin text-[10px]"></i>
                                                            <span class="runtime-display">Berjalan...</span>
                                                        </span>
                                                        @else
                                                        <span class="text-slate-400 text-xs">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Aksi --}}
                                                    <td class="text-center align-middle">
                                                        @if ($ploting->user_id)
                                                        @if ($ploting->status === null || $ploting->status === 'waiting')
                                                        @php
                                                        $missingDocsAssy = $planning->meja ? $planning->meja->getMissingDocuments() : ['Semua Dokumen'];
                                                        $isDocCompleteAssy = $planning->meja && count($missingDocsAssy) === 0;
                                                        @endphp
                                                        <button type="button" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm shadow-indigo-500/20 transition-all start-btn cursor-pointer"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            data-doc-complete="{{ $isDocCompleteAssy ? 'true' : 'false' }}"
                                                            data-missing-docs="{{ implode(', ', $missingDocsAssy) }}"
                                                            title="Start Produksi">
                                                            <i class="fas fa-play text-[10px]"></i>
                                                            <span>Start</span>
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'on_progress')
                                                        <button type="button" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm shadow-emerald-500/20 transition-all finish-btn cursor-pointer"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            title="Finish Produksi">
                                                            <i class="fas fa-stop text-[10px]"></i>
                                                            <span>Finish</span>
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'done')
                                                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                                            <i class="fas fa-check-circle text-[10px]"></i>Selesai
                                                        </span>
                                                        @endif
                                                        @else
                                                        <small class="text-slate-400 text-xs">
                                                            <i class="fas fa-info-circle mr-0.5"></i>Assign dulu
                                                        </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-slate-400 py-3 text-xs">
                                                        <i class="fas fa-users-slash mr-1.5"></i>Belum ada operator
                                                    </td>
                                                </tr>
                                                @endforelse

                                                {{-- Form tambah operator baru --}}
                                                <tr class="bg-slate-50/70 dark:bg-slate-800/50">
                                                    <td colspan="6" class="p-2">
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="flex items-center justify-center gap-2 add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 grup-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Pilih Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 operator-select"
                                                                style="width: 150px;" required>
                                                                <option value="">Pilih Operator</option>
                                                            </select>

                                                            <button type="button" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all add-operator-btn cursor-pointer" title="Tambah Operator">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                                <span>Tambah</span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-400 py-6 text-xs">
                                    <i class="fas fa-clipboard-list mr-1.5"></i>Tidak ada planning produksi Assembly
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Alert jika tidak ada data sama sekali --}}
    @if($planningsInject->count() == 0 && $planningsAssy->count() == 0)
    <div class="bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-900/60 rounded-2xl p-8 sm:p-12 text-center shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 dark:text-amber-400 flex items-center justify-center text-2xl mx-auto mb-4 border border-amber-200/60 dark:border-amber-800/40">
            <i class="fas fa-calendar-times"></i>
        </div>
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-1">
            Tidak Ada Planning Produksi Approved
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-0 leading-relaxed">
            Tidak ditemukan planning produksi yang sudah disetujui (approved) untuk tanggal ini. Silakan pilih tanggal lain atau tunggu approval dari Kabag PPIC.
        </p>
    </div>
    @endif
</div>

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
            <div class="modal-header px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0" id="confirmModalLabel">
                        Konfirmasi Aksi
                    </h5>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-5 text-xs text-slate-700 dark:text-slate-300" id="confirmModalBody">
                <!-- Konten akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer px-5 py-3 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-all cursor-pointer" id="confirmAction">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include Modal Show Planning --}}
@include('planning.show')
@endsection

@push('scripts')
<!-- DataTables JS sudah di-load di layouts/app.blade.php -->
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Set Laravel routes and variables for ploting.js
    window.plotingRoutes = {
        operatorsByGroup: "{{ route('ploting.operators') }}",
        plotingStart: "{{ url('ploting') }}/",
        plotingFinish: "{{ url('ploting') }}/",
        plotingRuntime: "{{ url('ploting') }}/",
        csrfToken: "{{ csrf_token() }}"
    };

    // Set variables for ploting-index.js
    window.plotingTodayYmd = "{{ date('Y-m-d') }}";
    window.plotingTomorrowYmd = "{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}";
    window.plotingCurrentYmd = "{{ request('tanggal', date('Y-m-d')) }}";
</script>

<!-- Custom Ploting Index JS -->
<script src="{{ asset('js/ploting-index.js') }}"
    data-is-leader-inject="{{ isset($isLeaderInject) && $isLeaderInject ? 'true' : 'false' }}"
    data-is-leader-assy="{{ isset($isLeaderAssy) && $isLeaderAssy ? 'true' : 'false' }}">
</script>

<!-- Custom Ploting JS -->
<script src="{{ asset('js/ploting.js') }}"></script>
@endpush