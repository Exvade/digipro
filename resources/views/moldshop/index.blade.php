@extends('moldshop.app')

@section('title', 'Data Master Mold - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Moldshop CSS -->
<link rel="stylesheet" href="{{ asset('css/moldshop.css') }}?v={{ filemtime(public_path('css/moldshop.css')) }}">
@endpush

@php
    $totalMold = $moldshops->count();
    $totalOk = $moldshops->where('status', 'OK')->count();
    $totalRepair = $moldshops->where('status', 'REPAIR')->count();
    $totalPreventive = $moldshops->where('status', 'PREVENTIVE')->count();
@endphp

@section('content-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-tools"></i>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Data Master Mold
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/80">
                    {{ $totalMold }} Unit
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola spesifikasi, nomor mold, status operasional, dan riwayat penggunaan cetakan
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
        <a href="{{ route('moldshop.history') }}" class="px-4 py-2.5 rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 font-bold text-xs inline-flex items-center gap-2 shadow-sm hover:shadow transition-all duration-200 hover:-translate-y-0.5">
            <i class="fas fa-history text-indigo-500"></i>
            <span>Riwayat Mold</span>
        </a>
        <button type="button" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white font-extrabold text-xs inline-flex items-center gap-2 shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/35 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer" data-toggle="modal" data-target="#modalCreate">
            <i class="fas fa-plus"></i>
            <span>Tambah Mold</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI / Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Mold -->
        <div class="kpi-card stat-filter-card kpi-card-indigo active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Mold
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-slate-900 dark:text-white" data-target="{{ $totalMold }}">
                        {{ number_format($totalMold) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Unit</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-b-2xl"></div>
        </div>

        <!-- Mold Status OK -->
        <div class="kpi-card stat-filter-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="OK">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Siap Pakai (OK)
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-target="{{ $totalOk }}">
                        {{ number_format($totalOk) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                        {{ $totalMold > 0 ? round(($totalOk / $totalMold) * 100) : 0 }}% Unit
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        <!-- Mold Status REPAIR -->
        <div class="kpi-card stat-filter-card kpi-card-rose bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="REPAIR">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Dalam Perbaikan
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-rose-600 dark:text-rose-400" data-target="{{ $totalRepair }}">
                        {{ number_format($totalRepair) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Repair</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-wrench"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-600 rounded-b-2xl hidden"></div>
        </div>

        <!-- Mold Status PREVENTIVE -->
        <div class="kpi-card stat-filter-card kpi-card-amber bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="PREVENTIVE">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Jadwal Preventive
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-amber-500 dark:text-amber-400" data-target="{{ $totalPreventive }}">
                        {{ number_format($totalPreventive) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Preventive</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-b-2xl hidden"></div>
        </div>
    </div>

    <!-- Main Table Card Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden transition-colors duration-300">
        <!-- Card Header Toolbar -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-table-list text-indigo-600 dark:text-indigo-400 text-sm"></i>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Daftar Master Mold
                </h3>
            </div>

            <!-- Quick Status Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mr-1 hidden sm:inline">Filter:</span>
                <button type="button" class="table-filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm" data-status="">
                    Semua ({{ $totalMold }})
                </button>
                <button type="button" class="table-filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-600" data-status="OK">
                    OK ({{ $totalOk }})
                </button>
                <button type="button" class="table-filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:text-rose-600" data-status="REPAIR">
                    REPAIR ({{ $totalRepair }})
                </button>
                <button type="button" class="table-filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-amber-400 hover:text-amber-600" data-status="PREVENTIVE">
                    PREVENTIVE ({{ $totalPreventive }})
                </button>
            </div>
        </div>

        <!-- Table Body -->
        <div class="p-4 sm:p-6">
            <div class="table-responsive">
                <table id="moldTable" class="table align-middle w-full">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="min-width: 140px;">No Mold</th>
                            <th style="min-width: 180px;">Nama Mold</th>
                            <th style="min-width: 120px;">Tipe / Cavity</th>
                            <th style="min-width: 140px;">Customer</th>
                            <th class="text-center" style="min-width: 130px;">Status</th>
                            <th class="text-center" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($moldshops as $index => $mold)
                        <tr>
                            <!-- No -->
                            <td class="text-center font-semibold text-slate-400 dark:text-slate-500 text-xs">
                                {{ $index + 1 }}
                            </td>

                            <!-- No Mold -->
                            <td>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-200/90 dark:border-slate-700 shadow-2xs">
                                    <i class="fas fa-hashtag text-[10px] text-indigo-500"></i>
                                    {{ $mold->no_mold }}
                                </span>
                            </td>

                            <!-- Nama Mold -->
                            <td>
                                <div class="font-extrabold text-xs text-slate-900 dark:text-white leading-snug">
                                    {{ $mold->nama_mold }}
                                </div>
                            </td>

                            <!-- Type -->
                            <td>
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
                                    {{ $mold->type ?? '-' }}
                                </span>
                            </td>

                            <!-- Customer -->
                            <td>
                                <div class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 font-medium">
                                    <i class="fas fa-building text-[11px] text-slate-400"></i>
                                    <span>{{ $mold->customer ?? '-' }}</span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="text-center">
                                @if ($mold->status == 'OK')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/90 dark:border-emerald-800/80 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    OK
                                </span>
                                @elseif ($mold->status == 'REPAIR')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/90 dark:border-rose-800/80 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    REPAIR
                                </span>
                                @elseif ($mold->status == 'PREVENTIVE')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/90 dark:border-amber-800/80 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    PREVENTIVE
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    {{ ucfirst($mold->status ?? '-') }}
                                </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button"
                                        class="editBtn w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-500 text-amber-600 dark:text-amber-400 hover:text-white border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-xs transition-all duration-200 shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer"
                                        title="Edit Mold"
                                        data-id="{{ $mold->id }}"
                                        data-no_mold="{{ $mold->no_mold }}"
                                        data-nama_mold="{{ $mold->nama_mold }}"
                                        data-type="{{ $mold->type }}"
                                        data-customer="{{ $mold->customer }}"
                                        data-status="{{ $mold->status }}">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('moldshop.destroy', $mold->id) }}"
                                        method="POST"
                                        class="deleteForm inline-block m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-500 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-200/80 dark:border-rose-800/80 flex items-center justify-center text-xs transition-all duration-200 shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer"
                                            title="Hapus Mold">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Include Modern Modals --}}
@include('moldshop.create')
@include('moldshop.update')
@endsection

@push('scripts')
<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<!-- Filter Script Integration -->
<script>
$(document).ready(function() {
    // KPI Counters Animation
    initKpiCounters();

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var isDecimal = $this.data('decimal') === true || $this.data('decimal') === 'true';
            var target = parseFloat($this.data('target')) || 0;
            if (target === 0) {
                $this.text(isDecimal ? '0.00' : '0');
                return;
            }

            var duration = 750;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = start + (target - start) * ease;

                if (isDecimal) {
                    $this.text(currentVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                } else {
                    $this.text(Math.floor(currentVal).toLocaleString());
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    if (isDecimal) {
                        $this.text(target.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    } else {
                        $this.text(target.toLocaleString());
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    // Quick Filter by status via KPI cards & pills
    function filterByStatus(status) {
        if ($.fn.DataTable.isDataTable('#moldTable')) {
            const dt = $('#moldTable').DataTable();
            // Column index 5 is Status
            dt.column(5).search(status ? '^' + status + '$' : '', true, false).draw();
        }

        // Update pills state
        $('.table-filter-pill').each(function() {
            const pillStatus = $(this).data('status');
            if (pillStatus === status) {
                $(this).removeClass('bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700')
                       .addClass('bg-indigo-600 text-white shadow-sm');
            } else {
                $(this).removeClass('bg-indigo-600 text-white shadow-sm')
                       .addClass('bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700');
            }
        });

        // Update KPI Card active states & indicator
        $('.stat-filter-card').removeClass('active ring-2 ring-indigo-500/20 ring-emerald-500/20 ring-rose-500/20 ring-amber-500/20');
        $('.stat-filter-card .kpi-active-indicator').addClass('hidden');

        var $activeCard = $('.stat-filter-card[data-filter="' + (status || '') + '"]');
        if ($activeCard.length) {
            var ringClass = 'ring-2 ring-indigo-500/20';
            if (status === 'OK') ringClass = 'ring-2 ring-emerald-500/20';
            else if (status === 'REPAIR') ringClass = 'ring-2 ring-rose-500/20';
            else if (status === 'PREVENTIVE') ringClass = 'ring-2 ring-amber-500/20';

            $activeCard.addClass('active ' + ringClass);
            $activeCard.find('.kpi-active-indicator').removeClass('hidden');
        }
    }

    // Pill click
    $(document).on('click', '.table-filter-pill', function() {
        const status = $(this).data('status');
        filterByStatus(status);
    });

    // KPI Card click
    $(document).on('click', '.stat-filter-card', function() {
        const status = $(this).data('filter');
        const isAlreadyActive = $(this).hasClass('active');

        if (isAlreadyActive && status !== '') {
            // Deselect to all
            filterByStatus('');
        } else {
            filterByStatus(status);
        }

        $('html, body').animate({
            scrollTop: $('#moldTable').offset().top - 120
        }, 300);
    });
});
</script>

@if(session('success'))
<div id="success-message" data-message="{{ session('success') }}" style="display:none;"></div>
@endif
@if(session('error'))
<div id="error-message" data-message="{{ session('error') }}" style="display:none;"></div>
@endif
@endpush