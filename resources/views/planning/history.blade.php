@extends('layouts.app')

@section('title', 'Riwayat Planning Produksi - Digital Production System')

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-blue-500 text-white flex items-center justify-center text-xl shadow-lg shadow-purple-500/25 flex-shrink-0">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Riwayat Planning Produksi
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $plannings->total() }} Arsip Ditemukan</span>
                </span>
                @if($filterTanggal)
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center gap-1">
                    <i class="far fa-calendar text-[10px]"></i>
                    <span>{{ \Carbon\Carbon::parse($filterTanggal)->translatedFormat('d M Y') }}</span>
                </span>
                @endif
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Arsip dan rekam jejak historis rencana produksi harian
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        {{-- Cetak Planning --}}
        <button type="button" 
            data-toggle="modal" 
            data-target="#modalCetak" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 transition-all cursor-pointer">
            <i class="fas fa-print text-xs"></i>
            <span>Cetak Planning</span>
        </button>

        <a href="{{ route('planning.index') }}" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-750 shadow-sm transition-all cursor-pointer">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Kembali ke Planning</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Filter Section --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Bagian Tab Pills --}}
        <div>
            <ul class="bagian-tabs flex items-center gap-1.5 list-none p-0 m-0" id="bagianTabsHistory" role="tablist">
                <li class="nav-item m-0">
                    <a class="nav-link active inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all" id="inject-tab-history" href="#" data-bagian="inject">
                        <i class="fas fa-cog"></i>
                        <span>Bagian Inject</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Filter Tanggal Controls --}}
        <form method="GET" action="{{ route('planning.history') }}" class="flex items-center gap-2.5 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                <i class="far fa-calendar-alt text-xs"></i>
                <span>Pilih Tanggal:</span>
            </span>
            <input type="date" 
                name="tanggal" 
                id="tanggal" 
                class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                value="{{ request('tanggal', $filterTanggal ?? date('Y-m-d', strtotime('-1 day'))) }}">
            <button type="submit" class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('planning.history') }}" class="px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer" title="Reset Filter">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0 flex items-center gap-2">
                    <i class="fas fa-folder-open text-purple-500"></i>
                    <span>Arsip Data Planning Produksi</span>
                </h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                    Menampilkan rekaman planning yang telah tersimpan di sistem
                </p>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar p-6">
            <table id="historyTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/60">
                        <th class="py-3 px-3 text-center rounded-l-xl" style="width: 50px;">No</th>
                        <th class="py-3 px-4">No Part</th>
                        <th class="py-3 px-4">Nama Part</th>
                        <th class="py-3 px-4 text-center">Bagian</th>
                        <th class="py-3 px-4 text-center">Mesin</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Qty Planning</th>
                        <th class="py-3 px-4 text-center rounded-r-xl" style="width: 120px;">Aksi</th>
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
                            <button type="button" 
                                class="w-8 h-8 mx-auto rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white dark:hover:bg-sky-600 dark:hover:text-white border border-sky-200/80 dark:border-sky-800/60 flex items-center justify-center transition-all showBtn cursor-pointer" 
                                title="Lihat Detail"
                                data-id="{{ $planning->id }}"
                                data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                data-tanggal="{{ $planning->tanggal }}"
                                data-lot="{{ $planning->lot_produksi }}"
                                data-no_part="{{ $planning->part->no_parts ?? '-' }}"
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
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($plannings->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex justify-center">
            {{ $plannings->links() }}
        </div>
        @endif
    </div>

</div>

{{-- === MODALS === --}}
@include('planning.show')

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
        "currentFilter": @json($filterTanggal)
    }
</script>
<script src="{{ asset('js/planning.js') }}?v={{ filemtime(public_path('js/planning.js')) }}"></script>
@endpush