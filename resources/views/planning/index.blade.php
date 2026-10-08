@extends('layouts.app')

@section('title', 'Planning Produksi - Digital Production System')

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Planning Produksi
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $plannings->total() }} Data Terdaftar</span>
                </span>
                @isset($filterTanggal)
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center gap-1">
                    <i class="far fa-calendar text-[10px]"></i>
                    <span>{{ \Carbon\Carbon::parse($filterTanggal)->translatedFormat('d M Y') }}</span>
                </span>
                @endisset
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Manajemen rencana kuantitas dan alokasi mesin untuk siklus harian produksi
            </p>
        </div>
    </div>

    {{-- Header Action Buttons --}}
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Cetak Planning --}}
        <button type="button" 
            data-toggle="modal" 
            data-target="#modalCetak" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 transition-all cursor-pointer">
            <i class="fas fa-print text-xs"></i>
            <span>Cetak Planning</span>
        </button>

        {{-- Tambah Planning --}}
        <button type="button" 
            data-toggle="modal" 
            data-target="#modalCreate" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-plus text-xs"></i>
            <span>Tambah Planning</span>
        </button>

        {{-- Import Excel --}}
        <button type="button" 
            data-toggle="modal" 
            data-target="#modalImport" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-750 shadow-sm transition-all cursor-pointer">
            <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400 text-xs"></i>
            <span>Import</span>
        </button>

        {{-- Approval Planning (Kabag / Manager / Super Admin) --}}
        @if(Auth::check() && (Auth::user()->role === 'super_admin' || Auth::user()->role === 'manager_plant' || (Auth::user()->role === 'kabag' && in_array(Auth::user()->departemen, ['PPIC', 'Production Planning Inventory Control']))))
        @php
            $pendingCountHeader = \App\Models\PlanningProduksi::where('status_approval', 'pending')->count();
        @endphp
        <a href="{{ route('planning.approval.page') }}" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white shadow-md shadow-amber-500/25 transition-all cursor-pointer">
            <i class="fas fa-check-circle text-xs"></i>
            <span>Approval</span>
            @if($pendingCountHeader > 0)
            <span class="keep-white inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full text-[10px] font-black bg-white text-amber-800 ml-0.5 shadow-sm leading-none" style="background-color: #ffffff !important; color: #92400e !important;">
                {{ $pendingCountHeader }}
            </span>
            @endif
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 shadow-sm animate-fade-in" role="alert">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-base"></i>
            </div>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" class="w-7 h-7 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 flex items-center justify-center transition-all cursor-pointer" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 shadow-sm animate-fade-in" role="alert">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-circle text-base"></i>
            </div>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" class="w-7 h-7 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 flex items-center justify-center transition-all cursor-pointer" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    @if(session('import_errors'))
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 shadow-sm" role="alert">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2.5 font-bold text-sm">
                <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400"></i>
                <span>Terdapat kesalahan pada data impor:</span>
            </div>
            <button type="button" class="w-7 h-7 rounded-lg text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-900/50 flex items-center justify-center transition-all cursor-pointer" data-dismiss="alert" aria-label="Close">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <ul class="text-xs space-y-1 list-disc list-inside max-h-40 overflow-y-auto pl-1 custom-scrollbar">
            @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Statistics KPI Cards --}}
    @php
        $totalQty = $plannings->sum('qty_planning_produksi');
        $approvedCount = $plannings->where('status_approval', 'approved')->count();
        $pendingCount = $plannings->where('status_approval', 'pending')->count();
        $rejectedCount = $plannings->where('status_approval', 'rejected')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Planning --}}
        <div class="kpi-card kpi-card-indigo group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-default">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-indigo-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    Total Planning
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-slate-900 dark:text-white group-hover:scale-105 transition-transform origin-left inline-block">
                        {{ number_format($plannings->total()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Record</span>
                </div>
            </div>
            <div class="kpi-icon w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-600 group-hover:shadow-md group-hover:shadow-indigo-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-tasks"></i>
            </div>
        </div>

        {{-- Total Target Qty --}}
        <div class="kpi-card kpi-card-sky group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-default">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-sky-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                    Target Kuantitas
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 group-hover:scale-105 transition-transform origin-left inline-block">
                        {{ number_format($totalQty) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Pcs</span>
                </div>
            </div>
            <div class="kpi-icon kpi-icon-reverse w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-sky-500 group-hover:text-white dark:group-hover:bg-sky-500 group-hover:shadow-md group-hover:shadow-sky-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-cubes"></i>
            </div>
        </div>

        {{-- Status Approved --}}
        <div class="kpi-card kpi-card-emerald group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-default">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-emerald-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    Disetujui
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform origin-left inline-block">
                        {{ $approvedCount }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Planning</span>
                </div>
            </div>
            <div class="kpi-icon w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-emerald-500 group-hover:text-white dark:group-hover:bg-emerald-500 group-hover:shadow-md group-hover:shadow-emerald-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-check-double"></i>
            </div>
        </div>

        {{-- Status Pending --}}
        <div class="kpi-card kpi-card-amber group bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-default">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-gradient-to-br from-amber-500/15 to-transparent rounded-full blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
            <div class="space-y-1 relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                    Menunggu Approval
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400 group-hover:scale-105 transition-transform origin-left inline-block">
                        {{ $pendingCount }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Planning</span>
                </div>
            </div>
            <div class="kpi-icon kpi-icon-reverse w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white dark:group-hover:bg-amber-500 group-hover:shadow-md group-hover:shadow-amber-500/30 transition-all duration-300 relative z-10">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar & Navigation Tabs --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Bagian Tab Pills --}}
        <div>
            <ul class="bagian-tabs flex items-center gap-1.5 list-none p-0 m-0" id="bagianTabs" role="tablist">
                <li class="nav-item m-0">
                    <a class="nav-link active inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all" id="inject-tab" href="#" data-bagian="inject">
                        <i class="fas fa-cog"></i>
                        <span>Bagian Inject</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Filter Tanggal Controls --}}
        <form method="GET" action="{{ route('planning.index') }}" id="formFilterIndex" class="flex items-center gap-2.5 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                <i class="far fa-calendar-alt text-xs"></i>
                <span>Tanggal:</span>
            </span>
            <select class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="filterTanggalIndex">
                <option value="today">Hari Ini ({{ now()->format('d-m-Y') }})</option>
                <option value="tomorrow">Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                <option value="custom">Pilih Tanggal Spesifik...</option>
            </select>
            <input type="date" id="customDateIndex" name="tanggal" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" style="display: none;">
        </form>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0 flex items-center gap-2">
                    <i class="fas fa-list-check text-indigo-500"></i>
                    <span>Daftar Planning Produksi</span>
                </h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                    Menampilkan data planning berdasarkan tanggal dan bagian terpilih
                </p>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar p-6">
            <table id="planningTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/60">
                        <th class="py-3 px-3 text-center rounded-l-xl" style="width: 50px;">No</th>
                        <th class="py-3 px-4">No Part</th>
                        <th class="py-3 px-4">Nama Part</th>
                        <th class="py-3 px-4 text-center">Bagian</th>
                        <th class="py-3 px-4 text-center">Mesin / Alokasi</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Qty Planning</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Status Approval</th>
                        <th class="py-3 px-4 text-center rounded-r-xl" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                    @foreach ($plannings as $planning)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-3 text-center font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                            {{ $plannings->firstItem() + $loop->index }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 dark:text-white font-mono text-xs">
                                {{ $planning->part->no_parts ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                Lot: <span class="font-medium text-slate-600 dark:text-slate-300">{{ $planning->lot_produksi ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs">
                                {{ $planning->part->nama_part ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                Tipe: <span class="font-medium text-slate-600 dark:text-slate-300">{{ $planning->part->type ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($planning->bagian == 'assy')
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center gap-1">
                                <i class="fas fa-tools text-[10px]"></i> Assembly
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                                <i class="fas fa-cog text-[10px]"></i> Inject
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold">
                                <i class="fas fa-industry text-[10px] text-slate-400"></i>
                                @if($planning->bagian == 'assy')
                                {{ $planning->meja->nama_meja ?? '-' }}
                                @else
                                {{ $planning->mesin->nama_mesin ?? '-' }}
                                @endif
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-mono font-black text-xs text-indigo-600 dark:text-indigo-400">
                                {{ number_format($planning->qty_planning_produksi) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block font-medium">Pcs</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($planning->status_approval == 'approved')
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                <i class="fas fa-check-circle text-[10px]"></i> Approved
                            </span>
                            @if($planning->approved_at)
                            <span class="block text-[10px] font-medium text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($planning->approved_at)->format('d/m/Y H:i') }}
                            </span>
                            @endif
                            @elseif($planning->status_approval == 'rejected')
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60 inline-flex items-center gap-1">
                                <i class="fas fa-times-circle text-[10px]"></i> Rejected
                            </span>
                            @if($planning->approved_at)
                            <span class="block text-[10px] font-medium text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($planning->approved_at)->format('d/m/Y H:i') }}
                            </span>
                            @endif
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i> Pending
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                {{-- Tombol Detail --}}
                                <button type="button" 
                                    class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900 border border-sky-200/80 dark:border-sky-800/60 flex items-center justify-center transition-all showBtn cursor-pointer" 
                                    title="Lihat Detail"
                                    data-id="{{ $planning->id }}"
                                    data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                    data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                    data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                    data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                    data-tanggal="{{ $planning->tanggal }}"
                                    data-lot="{{ $planning->lot_produksi }}"
                                    data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                    data-no_barang="{{ $planning->part->no_barang ?? '-' }}"
                                    data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                    data-type="{{ $planning->part->type ?? '-' }}"
                                    data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                    data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                    data-catatan="{{ $planning->catatan ?? '' }}"
                                    data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                    data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                    data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                    data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                    data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'>
                                    <i class="fas fa-eye text-xs"></i>
                                </button>

                                {{-- Tombol Edit --}}
                                <button type="button" 
                                    class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-center transition-all editBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                    data-moldshop_id="{{ $planning->moldshop_id }}"
                                    data-mesin_id="{{ $planning->mesin_id }}"
                                    data-meja_id="{{ $planning->meja_id }}"
                                    data-part_id="{{ $planning->part_id }}"
                                    data-tanggal="{{ $planning->tanggal }}"
                                    data-lot_produksi="{{ $planning->lot_produksi }}"
                                    data-qty="{{ $planning->qty_planning_produksi }}"
                                    data-catatan="{{ $planning->catatan ?? '' }}"
                                    data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                    data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                    data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                    data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                    data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                    title="Edit Planning">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>

                                {{-- Tombol Approval Cepat (Kabag PPIC) --}}
                                @if(auth()->user()->role == 'kabag' && auth()->user()->departemen == 'PPIC' && $planning->status_approval == 'pending')
                                <button type="button" 
                                    class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center transition-all approvalBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    data-action="approve"
                                    title="Setujui Planning">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900 border border-rose-200/80 dark:border-rose-800/60 flex items-center justify-center transition-all approvalBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    data-action="reject"
                                    title="Tolak Planning">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                                @endif

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('planning.destroy', $planning->id) }}" method="POST" class="deleteForm inline-block m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900 border border-rose-200/80 dark:border-rose-800/60 flex items-center justify-center transition-all cursor-pointer" 
                                        title="Hapus Data">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($plannings->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex justify-center">
            {{ $plannings->appends(['tanggal' => $filterTanggal ?? null])->links() }}
        </div>
        @endif
    </div>

</div>

{{-- === MODALS === --}}
@include('planning.create')
@include('planning.update')
@include('planning.show')

{{-- Modal Approval --}}
<div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-labelledby="modalApprovalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between" id="approvalModalHeader">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalApprovalLabel">
                            Approval Planning
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Konfirmasi status verifikasi rencana produksi
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <form id="formApproval" method="POST">
                    @csrf
                    <input type="hidden" id="approval_planning_id" name="planning_id">
                    <input type="hidden" id="approval_action" name="action">

                    <div id="approveContent" style="display: none;">
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 text-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-emerald-600 mt-0.5"></i>
                                <div>
                                    <strong class="font-bold block mb-1">Konfirmasi Persetujuan</strong>
                                    Apakah Anda yakin ingin menyetujui planning produksi ini? QR Code approval resmi akan diterbitkan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="rejectContent" style="display: none;" class="space-y-4">
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-triangle text-rose-600 mt-0.5"></i>
                                <div>
                                    <strong class="font-bold block mb-1">Konfirmasi Penolakan</strong>
                                    Apakah Anda yakin ingin menolak planning produksi ini?
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="rejection_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all outline-none" 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                rows="3"
                                placeholder="Masukkan alasan penolakan..." required></textarea>
                            <small class="text-xs text-slate-400 mt-1 block">Wajib mencantumkan alasan penolakan agar pembuat planning dapat memperbaikinya</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer" id="submitApprovalBtn" onclick="submitApproval()">
                    <i class="fas fa-check mr-1.5"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Filter Cetak --}}
<div class="modal fade" id="modalCetak" tabindex="-1" role="dialog" aria-labelledby="modalCetakLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-print"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalCetakLabel">
                            Cetak Planning Produksi
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Pilih parameter filter cetak jadwal lembar kerja
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <form id="formCetak" method="GET" action="{{ route('planning.cetak') }}" target="_blank" data-base-url="{{ route('planning.cetak') }}">
                    <div>
                        <label for="tanggalFilter" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Pilih Tanggal Planning
                        </label>
                        <select class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="tanggalFilter" name="tanggal">
                            <option value="besok">📅 Planning Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                            <option value="hari_ini">📅 Planning Hari Ini ({{ now()->format('d-m-Y') }})</option>
                            <option value="custom">📅 Tanggal Spesifik</option>
                        </select>
                    </div>

                    <div id="customDateGroup" style="display: none;" class="mt-3">
                        <label for="customDate" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Tanggal Spesifik:
                        </label>
                        <input type="date" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="customDate" name="tanggal_custom">
                    </div>

                    <div class="mt-3">
                        <label for="bagianFilter" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Pilih Bagian Planning
                        </label>
                        <select class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="bagianFilter" name="bagian">
                            <option value="inject">⚙️ Inject Saja</option>
                        </select>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300 space-y-1 mt-4">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-indigo-500"></i> Informasi Format:
                        </div>
                        <p class="mb-0 text-[11px] text-slate-500 dark:text-slate-400">Lembar cetak akan dibuka di tab baru dan telah dioptimalkan untuk tata letak printer A4 Landscape.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer" onclick="submitCetak()">
                    <i class="fas fa-print mr-1.5"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalImportLabel">
                            Import Data Planning
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Unggah berkas spreadsheet format .xlsx atau .xls
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <form action="{{ route('planning.import-excel') }}" method="POST" enctype="multipart/form-data" id="formImport">
                    @csrf
                    
                    {{-- Download Template Banner --}}
                    <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-file-download text-indigo-600 dark:text-indigo-400 text-base"></i>
                            <div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Unduh Template Standar</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Pastikan kolom sesuai dengan format sistem</div>
                            </div>
                        </div>
                        <a href="{{ route('planning.download-template') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700 shadow-sm hover:bg-indigo-50 transition-all inline-flex items-center gap-1">
                            <i class="fas fa-download text-[10px]"></i>
                            <span>Template</span>
                        </a>
                    </div>

                    {{-- File Upload Area --}}
                    <div class="space-y-1.5">
                        <label for="excel_file" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Pilih Berkas Excel <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-indigo-500 transition-colors">
                            <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-xl">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 custom-file-label" for="excel_file">
                                    Pilih file atau drag & drop disini
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Format: .xlsx, .xls (Maksimum 2MB)
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition-all cursor-pointer" onclick="document.getElementById('formImport').submit()">
                    <i class="fas fa-upload mr-1.5"></i> Unggah & Impor
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/planning.css') }}?v={{ filemtime(public_path('css/planning.css')) }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="application/json" id="planning-data">
    {
        "successMessage": @json(session('success')),
        "indexUrl": "{{ route('planning.index') }}",
        "cetakUrl": "{{ route('planning.cetak') }}",
        "todayYmd": "{{ now()->format('Y-m-d') }}",
        "tomorrowYmd": "{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}",
        "currentFilter": @json($filterTanggal ?? null)
    }
</script>
<script src="{{ asset('js/planning.js') }}?v={{ filemtime(public_path('js/planning.js')) }}"></script>
@endpush