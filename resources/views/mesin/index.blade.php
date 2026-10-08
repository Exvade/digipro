@extends('layouts.app')

@section('title', 'Master Data Mesin - Digital Production System')

@push('styles')
<!-- Custom Mesin Tailwind & DataTables CSS -->
<link rel="stylesheet" href="{{ asset('css/mesin-index.css') }}?v={{ filemtime(public_path('css/mesin-index.css')) }}">
@endpush

@section('content-header')
@php
$user = Auth::user();
$isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
$canCreate = !in_array($user->role, ['leader', 'staff']) || $isRMStaff;
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-industry"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Data Master Mesin
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $mesins->count() }} Mesin Terdaftar</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Katalog spesifikasi teknis mesin injeksi, kapasitas tonase, brand, dan pemantauan status operasional
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if($canCreate)
        <button type="button" 
            class="btn-create-mesin inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-plus text-xs"></i>
            <span>Tambah Mesin</span>
        </button>
        @endif
    </div>
</div>
@endsection

@section('content')
@php
$user = Auth::user();
$isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
@endphp

<div class="space-y-6">
    {{-- Statistics KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Mesin --}}
        <div class="kpi-card kpi-card-indigo active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Mesin
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-slate-900 dark:text-white" id="totalMesinCount" data-target="{{ $mesins->count() }}">
                        {{ number_format($mesins->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Unit</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-industry"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-b-2xl"></div>
        </div>

        {{-- Mesin Running --}}
        <div class="kpi-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="running">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Mesin Beroperasi
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-target="{{ $mesins->where('status', 'running')->count() }}">
                        {{ number_format($mesins->where('status', 'running')->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Running</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-play"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Mesin Stand By --}}
        <div class="kpi-card kpi-card-amber bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="stop">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Mesin Stand By
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-amber-500 dark:text-amber-400" data-target="{{ $mesins->where('status', 'stop')->count() }}">
                        {{ number_format($mesins->where('status', 'stop')->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Stand By</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-pause"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Mesin Breakdown / Off --}}
        <div class="kpi-card kpi-card-rose bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="breakdown-offline">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Breakdown & Off
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-rose-600 dark:text-rose-400" data-target="{{ $mesins->where('status', 'maintenance')->count() }}">
                        {{ number_format($mesins->where('status', 'maintenance')->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                        / <span class="kpi-counter" data-target="{{ $mesins->where('status', 'offline')->count() }}">{{ number_format($mesins->where('status', 'offline')->count()) }}</span> Off
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-wrench"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-600 rounded-b-2xl hidden"></div>
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
        <button type="button" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 p-1 cursor-pointer" data-dismiss="alert" aria-label="Close">
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
        <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1 cursor-pointer" data-dismiss="alert" aria-label="Close">
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
        <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1 cursor-pointer" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-table-list"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Daftar Mesin Injeksi</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Semua katalog tonase, brand mesin, dan status operasional real-time</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Total: {{ $mesins->count() }} Mesin
            </span>
        </div>

        <div class="p-4 sm:p-5">
            <table id="mesinTable" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">No</th>
                        <th>Nama Mesin</th>
                        <th>Brand</th>
                        <th class="text-center">Tonase</th>
                        <th class="text-center">Status Operasional</th>
                        <th>Keterangan</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="mesinTableBody">
                    @forelse($mesins as $index => $mesin)
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-400 dark:text-slate-500">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $mesin->status === 'running' ? 'bg-emerald-500' : ($mesin->status === 'maintenance' ? 'bg-rose-500' : ($mesin->status === 'stop' ? 'bg-amber-500' : 'bg-slate-400')) }}"></span>
                                <span class="font-bold text-xs text-slate-900 dark:text-white">
                                    {{ $mesin->nama_mesin }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="font-mono text-xs text-slate-600 dark:text-slate-400">
                                {{ $mesin->brand_mesin ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 whitespace-nowrap">
                                {{ $mesin->tonase_mesin }} Ton
                            </span>
                        </td>
                        <td class="text-center">
                            @if($mesin->status == 'running')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span>Running</span>
                            </span>
                            @elseif($mesin->status == 'maintenance')
                            <div class="inline-flex flex-col items-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                                    <i class="fas fa-wrench text-[10px]"></i>
                                    <span>Breakdown</span>
                                </span>
                                @if($mesin->stop_reason)
                                <span class="text-[10px] text-rose-500 dark:text-rose-400 font-medium mt-0.5 max-w-[150px] truncate" title="{{ $mesin->stop_reason }}">
                                    {{ $mesin->stop_reason }}
                                </span>
                                @endif
                            </div>
                            @elseif($mesin->status == 'stop')
                            <div class="inline-flex flex-col items-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                                    <i class="fas fa-pause text-[10px]"></i>
                                    <span>Stand By</span>
                                </span>
                                @if($mesin->stop_reason)
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium mt-0.5 max-w-[150px] truncate" title="{{ $mesin->stop_reason }}">
                                    {{ $mesin->stop_reason }}
                                </span>
                                @endif
                            </div>
                            @elseif($mesin->status == 'offline')
                            <div class="inline-flex flex-col items-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <i class="fas fa-power-off text-[10px]"></i>
                                    <span>Off</span>
                                </span>
                                @if($mesin->stop_reason)
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5 max-w-[150px] truncate" title="{{ $mesin->stop_reason }}">
                                    {{ $mesin->stop_reason }}
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ ucfirst($mesin->status ?? '-') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400 line-clamp-1" title="{{ $mesin->keterangan ?? '-' }}">
                                {{ $mesin->keterangan ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- View Detail Button --}}
                                <button type="button" 
                                    class="btn-view-mesin w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-mesin-id="{{ $mesin->id }}"
                                    data-mesin-nama="{{ $mesin->nama_mesin }}"
                                    data-mesin-brand="{{ $mesin->brand_mesin }}"
                                    data-mesin-tonase="{{ $mesin->tonase_mesin }}"
                                    data-mesin-status="{{ $mesin->status }}"
                                    data-mesin-reason="{{ $mesin->stop_reason }}"
                                    data-mesin-keterangan="{{ $mesin->keterangan }}"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>

                                {{-- Edit Button for Super Admin / Manager / RM Staff --}}
                                @if(!in_array($user->role, ['leader', 'staff', 'operator']) || $isRMStaff)
                                <button type="button" 
                                    class="btn-edit-mesin w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-mesin-id="{{ $mesin->id }}" 
                                    title="Edit Mesin">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                @endif

                                {{-- Leader Status Update Button --}}
                                @if(in_array($user->role, ['leader']) && !$isRMStaff)
                                <button type="button" 
                                    class="btn-edit-mesin w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-mesin-id="{{ $mesin->id }}" 
                                    title="Update Status Operasional">
                                    <i class="fas fa-sync-alt text-xs"></i>
                                </button>
                                @endif

                                {{-- Delete Button for Super Admin / Manager / RM Staff --}}
                                @if(!in_array($user->role, ['leader', 'staff', 'operator']))
                                <button type="button" 
                                    class="btn-delete-mesin w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-mesin-id="{{ $mesin->id }}" 
                                    data-mesin-name="{{ $mesin->nama_mesin }}" 
                                    title="Hapus Mesin">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 dark:text-slate-500 py-10">
                            <i class="fas fa-industry text-3xl mb-3 block"></i>
                            <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak ada data mesin</h5>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0">Belum ada mesin yang terdaftar dalam sistem.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mesin -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="addModalLabel">
                            Tambah Mesin Baru
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Registrasi unit mesin injeksi baru ke sistem produksi
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="addModalBody">
                <!-- Form loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Mesin -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="editModalLabel">
                            Edit Data Mesin
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Perbarui spesifikasi atau status operasional mesin
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="editModalBody">
                <!-- Form loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Mesin -->
<div class="modal fade" id="viewMesinModal" tabindex="-1" aria-labelledby="viewMesinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="viewMesinModalLabel">
                            Detail Spesifikasi Mesin
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Rincian profil, kapasitas tonase, dan kondisi operasional mesin
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <div class="modal-body p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Nama Mesin
                        </span>
                        <p id="view_nama_mesin" class="text-sm font-black text-slate-900 dark:text-white mb-0"></p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Brand Mesin
                        </span>
                        <p id="view_brand_mesin" class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-300 mb-0"></p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Kapasitas Tonase
                        </span>
                        <p id="view_tonase_mesin" class="font-mono text-sm font-bold text-sky-600 dark:text-sky-400 mb-0"></p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Status Operasional
                        </span>
                        <div id="view_status_pill"></div>
                    </div>
                    <div id="view_reason_wrapper" class="text-right hidden">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Pesan / Alasan
                        </span>
                        <span id="view_stop_reason" class="text-xs font-semibold text-rose-600 dark:text-rose-400"></span>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                        Keterangan Tambahan
                    </span>
                    <p id="view_keterangan" class="text-xs text-slate-700 dark:text-slate-300 mb-0 leading-relaxed"></p>
                </div>
            </div>

            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" 
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" 
                    data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom Mesin JS -->
<script src="{{ asset('js/mesin-index.js') }}?v={{ filemtime(public_path('js/mesin-index.js')) }}"></script>
<script>
    window.createModalUrl = '{{ route("mesin.create") }}';
</script>
@endpush