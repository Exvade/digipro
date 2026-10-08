@extends('layouts.app')

@section('title', 'Master Data Parts - Digital Production System')

@push('styles')
<!-- Custom Parts CSS -->
<link rel="stylesheet" href="{{ asset('css/parts-index.css') }}?v={{ filemtime(public_path('css/parts-index.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-cubes"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Data Master Part
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $parts->count() }} Part Terdaftar</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Katalog spesifikasi nomor part, nomor barang, tipe, kuantitas, dan berat bruto produksi
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" 
            data-toggle="modal" 
            data-target="#createPartModal" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-plus text-xs"></i>
            <span>Tambah Part</span>
        </button>

    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Statistics KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Part --}}
        <div class="kpi-card kpi-card-indigo active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Part
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-slate-900 dark:text-white" id="totalParts" data-target="{{ $parts->count() }}">
                        {{ number_format($parts->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Katalog</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-b-2xl"></div>
        </div>

        {{-- Total Quantity --}}
        <div class="kpi-card kpi-card-sky bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="quantity">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Quantity
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-sky-600 dark:text-sky-400" data-target="{{ $parts->sum('quantity') }}">
                        {{ number_format($parts->sum('quantity')) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Pcs / Unit</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-sky-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Total Bruto --}}
        <div class="kpi-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="bruto">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Bruto
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-target="{{ $parts->sum('bruto') }}" data-decimal="true">
                        {{ number_format($parts->sum('bruto'), 2) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Kg</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-weight-hanging"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Ragam Tipe --}}
        <div class="kpi-card kpi-card-amber bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="type">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Ragam Tipe
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-amber-500 dark:text-amber-400" data-target="{{ $parts->pluck('type')->unique()->filter()->count() }}">
                        {{ number_format($parts->pluck('type')->unique()->filter()->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Kategori</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-b-2xl hidden"></div>
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

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-table-list"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Daftar Master Part</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Semua katalog dan spesifikasi teknis part produksi</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Total: {{ $parts->count() }} Data
            </span>
        </div>

        <div class="p-4 sm:p-5">
            <table id="partsTable" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">No</th>
                        <th>No Part</th>
                        <th>No Barang</th>
                        <th>Nama Part</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Bruto (kg)</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="partsTableBody">
                    @forelse($parts as $index => $part)
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-400 dark:text-slate-500">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $part->no_parts }}
                            </span>
                        </td>
                        <td>
                            <span class="font-mono text-xs text-slate-600 dark:text-slate-400">
                                {{ $part->no_barang ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200" title="{{ $part->nama_part }}">
                                {{ $part->nama_part }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                {{ $part->type }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 whitespace-nowrap">
                                {{ number_format($part->quantity) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 whitespace-nowrap">
                                {{ number_format($part->bruto, 2) }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                    class="btn-view-part w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-part-id="{{ $part->id }}" 
                                    title="Lihat Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="btn-edit-part w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-part-id="{{ $part->id }}" 
                                    title="Edit Part">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="btn-delete-part w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800/60 inline-flex items-center justify-center transition-all cursor-pointer" 
                                    data-part-id="{{ $part->id }}" 
                                    data-part-name="{{ $part->nama_part }}" 
                                    title="Hapus Part">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-400 dark:text-slate-500 py-10">
                            <i class="fas fa-inbox text-3xl mb-3 block"></i>
                            <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak ada data parts</h5>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0">Belum ada data parts yang tersedia. Silakan klik "Tambah Part" untuk menambahkan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Create Modal -->
@include('parts.create')

<!-- Include Edit Modal -->
@include('parts.update')

<!-- View Part Modal -->
<div class="modal fade" id="viewPartModal" tabindex="-1" aria-labelledby="viewPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="viewPartModalLabel">
                            Detail Spesifikasi Part
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Rincian katalog dan spesifikasi teknis part
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
                            No Part
                        </span>
                        <p id="view_no_parts" class="font-mono text-sm font-bold text-indigo-600 dark:text-indigo-400 mb-0"></p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            No Barang
                        </span>
                        <p id="view_no_barang" class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-300 mb-0"></p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                            Type Part
                        </span>
                        <p id="view_type" class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-0"></p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                        Nama Part
                    </span>
                    <p id="view_nama_part" class="text-sm font-bold text-slate-900 dark:text-white mb-0"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                                Quantity (Stok / Unit)
                            </span>
                            <p id="view_quantity" class="font-mono text-xl font-black text-sky-600 dark:text-sky-400 mb-0"></p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300">Pcs</span>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                                Berat Bruto
                            </span>
                            <p id="view_bruto" class="font-mono text-xl font-black text-emerald-600 dark:text-emerald-400 mb-0"></p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">Kg</span>
                    </div>
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
<!-- Custom Parts JS -->
<script src="{{ asset('js/parts-index.js') }}?v={{ filemtime(public_path('js/parts-index.js')) }}"></script>
@endpush