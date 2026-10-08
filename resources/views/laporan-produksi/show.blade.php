@extends('layouts.app')

@section('title', 'Detail Laporan Produksi #' . $data->id . ' - Digital Production System')

@php
    $planning = $data->planning;
    $bagian = strtolower($planning->bagian ?? 'inject');
    $isAssy = ($bagian === 'assy');

    $targetPlan = $planning->qty_planning_produksi ?? 0;
    $okCount = $data->ok_count ?? 0;
    $ngCount = $data->ng_count ?? 0;
    $totalActual = $okCount + $ngCount;
    $achievement = $targetPlan > 0 ? round(($okCount / $targetPlan) * 100, 1) : 0;
    $defectRatio = $totalActual > 0 ? round(($ngCount / $totalActual) * 100, 2) : 0;
@endphp

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <a href="{{ route('laporan-produksi.index') }}" class="w-11 h-11 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 flex items-center justify-center text-sm shadow-sm transition-all cursor-pointer hover:-translate-x-0.5" title="Kembali ke Daftar Laporan">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Laporan Produksi #{{ $data->id }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 inline-flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Status: Approved</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isAssy ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/80' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200/80' }}">
                    {{ $isAssy ? 'Bagian Assembly' : 'Bagian Inject' }}
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0 flex items-center gap-2 flex-wrap">
                <span><i class="far fa-calendar-alt text-rose-500 mr-1"></i>{{ $data->created_at->format('d F Y, H:i') }} WIB</span>
                <span>&bull;</span>
                <span><i class="fas fa-user-check text-emerald-500 mr-1"></i>Disetujui oleh: <strong>{{ $data->approver->nama ?? 'Leader' }}</strong></span>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('laporan-produksi.cetak-single', $data->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-2 shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
            <i class="fas fa-print text-sm"></i>
            <span>Cetak Dokumen PDF</span>
        </a>
        <a href="{{ route('laporan-produksi.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs inline-flex items-center gap-1.5 transition-all">
            <i class="fas fa-list text-xs"></i>
            <span>Semua Laporan</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Overview Grid Cards (Part Info & KPIs) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Card 1: Identitas Part & Proses --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-cube"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 mb-0">
                        Identitas Part & Proses
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Spesifikasi mold dan mesin produksi</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Nama Part</span>
                    <strong class="text-slate-900 dark:text-white font-bold block truncate" title="{{ $planning->part->nama_part ?? '-' }}">
                        {{ $planning->part->nama_part ?? '-' }}
                    </strong>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Part Number</span>
                    <strong class="text-slate-900 dark:text-white font-mono font-bold block truncate" title="{{ $planning->part->no_parts ?? '-' }}">
                        {{ $planning->part->no_parts ?? '-' }}
                    </strong>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">
                        {{ $isAssy ? 'Meja Kerja' : 'Mesin Produksi' }}
                    </span>
                    <strong class="text-slate-900 dark:text-white font-bold block truncate">
                        {{ $isAssy ? ($planning->meja->nama_meja ?? '-') : ($planning->mesin->nama_mesin ?? '-') }}
                    </strong>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Customer</span>
                    <strong class="text-slate-900 dark:text-white font-bold block truncate" title="{{ optional($planning->moldshop)->customer ?? optional($planning->moldshop)->customer_name ?? '-' }}">
                        {{ optional($planning->moldshop)->customer ?? optional($planning->moldshop)->customer_name ?? '-' }}
                    </strong>
                </div>

                <div class="col-span-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Mold Info</span>
                    <strong class="text-slate-900 dark:text-white font-bold block truncate">
                        {{ optional($planning->moldshop)->nama_mold ?? optional($planning->moldshop)->kode_mold ?? '-' }}
                    </strong>
                </div>
            </div>
        </div>

        {{-- Card 2: Metrik & Pencapaian Produksi --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 mb-0">
                        Pencapaian Produksi
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Perbandingan output terhadap target</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/70 dark:border-indigo-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block mb-0.5">Target Planning</span>
                    <span class="text-xl font-black font-mono text-indigo-950 dark:text-indigo-100">{{ number_format($targetPlan) }}</span>
                    <span class="text-[10px] text-indigo-500 dark:text-indigo-400 block">pcs</span>
                </div>

                <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/70 dark:border-emerald-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-0.5">Total Output OK</span>
                    <span class="text-xl font-black font-mono text-emerald-950 dark:text-emerald-100">{{ number_format($okCount) }}</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block">Pencapaian: {{ $achievement }}%</span>
                </div>

                <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/70 dark:border-rose-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-0.5">Total Reject / NG</span>
                    <span class="text-xl font-black font-mono text-rose-950 dark:text-rose-100">{{ number_format($ngCount) }}</span>
                    <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold block">Rasio NG: {{ $defectRatio }}%</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 block mb-0.5">Total Produksi</span>
                    <span class="text-xl font-black font-mono text-slate-900 dark:text-white">{{ number_format($totalActual) }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">OK + NG</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Operator & Verifikasi Approval --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 mb-0">
                        Status Verifikasi & Tim
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Approval leader dan tim operator</p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/50 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Approver Leader</span>
                        <div class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">
                            {{ $data->approver->nama ?? 'Leader Produksi' }}
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                            {{ $data->updated_at ? $data->updated_at->format('d M Y, H:i') : '-' }} WIB
                        </div>
                    </div>
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-500 text-white text-sm shadow-sm shadow-emerald-500/20">
                        <i class="fas fa-check"></i>
                    </span>
                </div>

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1.5">Operator Terploting</span>
                    <div class="flex flex-wrap gap-1.5">
                        @if($planning && $planning->plotings && $planning->plotings->count() > 0)
                            @foreach($planning->plotings as $ploting)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-200 dark:border-slate-700">
                                <i class="fas fa-user-circle text-rose-500 text-[10px]"></i>
                                <span>{{ $ploting->operator->nama ?? '-' }}</span>
                            </span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-400">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist Card: Persiapan Proses Produksi (SOP & 5R) --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-tasks"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight mb-0">
                        Persiapan Proses Produksi (Checklist SOP & 5R)
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                        Pemeriksaan kesiapan kerja, kelengkapan dokumen, dan keselamatan kerja per shift
                    </p>
                </div>
            </div>
            <span class="hidden sm:inline-flex px-3 py-1 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                9 Poin Standar
            </span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-4 w-12 text-center" rowspan="2">No</th>
                        <th class="py-3 px-4 min-w-[260px]" rowspan="2">Aktivitas Pemeriksaan</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="2">Shift 1 (Pagi)</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="2">Shift 2 (Siang)</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="2">Shift 3 (Malam)</th>
                        <th class="py-3 px-4 min-w-[200px] border-l border-slate-200 dark:border-slate-700" rowspan="2">Keterangan / Tindak Lanjut</th>
                    </tr>
                    <tr class="bg-slate-50/60 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold">
                        <th class="py-1.5 px-3 text-center border-l border-slate-200 dark:border-slate-700 w-16">OP</th>
                        <th class="py-1.5 px-3 text-center w-16">Leader</th>
                        <th class="py-1.5 px-3 text-center border-l border-slate-200 dark:border-slate-700 w-16">OP</th>
                        <th class="py-1.5 px-3 text-center w-16">Leader</th>
                        <th class="py-1.5 px-3 text-center border-l border-slate-200 dark:border-slate-700 w-16">OP</th>
                        <th class="py-1.5 px-3 text-center w-16">Leader</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                    $checklistItems = [
                        'Briefing dan tukar informasi',
                        'Kesiapan Produk WIP & Subpart',
                        'Kesiapan Packing',
                        'Meja proses tersedia, lampu penerangan menyala',
                        'Kelengkapan dokumen IK, Q-Point, dan Std Packing',
                        'Pengecekan 5R',
                        'Pemeriksaan kondisi subpart (sesuai dengan planning, CKD, CBU, Reguler)',
                        'Kesiapan Tag Label, Marker, dan Cutter',
                        'Ketersediaan limit sampel produk'
                    ];

                    $persiapanData = $persiapanData ?? ['Pagi' => [], 'Siang' => [], 'Malam' => []];
                    $pagiData = $persiapanData['Pagi'] ?? [];
                    $siangData = $persiapanData['Siang'] ?? [];
                    $malamData = $persiapanData['Malam'] ?? [];
                    @endphp

                    @foreach($checklistItems as $idx => $item)
                    @php
                        $pagiItem = collect($pagiData)->firstWhere('item_name', $item);
                        $siangItem = collect($siangData)->firstWhere('item_name', $item);
                        $malamItem = collect($malamData)->firstWhere('item_name', $item);
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4 text-center font-bold text-slate-400 dark:text-slate-500">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $item }}
                        </td>

                        {{-- Shift 1 --}}
                        <td class="py-3 px-3 text-center border-l border-slate-100 dark:border-slate-800">
                            @if($pagiItem && $pagiItem['operator_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                                <i class="fas fa-check"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($pagiItem && $pagiItem['leader_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                <i class="fas fa-check-double"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>

                        {{-- Shift 2 --}}
                        <td class="py-3 px-3 text-center border-l border-slate-100 dark:border-slate-800">
                            @if($siangItem && $siangItem['operator_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                                <i class="fas fa-check"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($siangItem && $siangItem['leader_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                <i class="fas fa-check-double"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>

                        {{-- Shift 3 --}}
                        <td class="py-3 px-3 text-center border-l border-slate-100 dark:border-slate-800">
                            @if($malamItem && $malamItem['operator_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                                <i class="fas fa-check"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($malamItem && $malamItem['leader_checked'])
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                <i class="fas fa-check-double"></i>
                            </span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600 font-bold">-</span>
                            @endif
                        </td>

                        {{-- Keterangan --}}
                        <td class="py-3 px-4 border-l border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                            <div class="space-y-1">
                                @if($pagiItem && $pagiItem['keterangan'])
                                <div class="text-[11px]"><span class="font-bold text-slate-700 dark:text-slate-300">Pagi:</span> {{ $pagiItem['keterangan'] }}</div>
                                @endif
                                @if($siangItem && $siangItem['keterangan'])
                                <div class="text-[11px]"><span class="font-bold text-slate-700 dark:text-slate-300">Siang:</span> {{ $siangItem['keterangan'] }}</div>
                                @endif
                                @if($malamItem && $malamItem['keterangan'])
                                <div class="text-[11px]"><span class="font-bold text-slate-700 dark:text-slate-300">Malam:</span> {{ $malamItem['keterangan'] }}</div>
                                @endif
                                @if(!($pagiItem && $pagiItem['keterangan']) && !($siangItem && $siangItem['keterangan']) && !($malamItem && $malamItem['keterangan']))
                                <span class="text-slate-300 dark:text-slate-600 text-[11px]">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Main Hourly Table: Pencapaian Proses Produksi Per Jam --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight mb-0">
                        Pencapaian Proses Produksi Per Jam
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                        Monitoring rinci output OK, NG Part, dan NG Subpart setiap jam kerja
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                24 Jam Siklus Kerja
            </span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-3 text-center min-w-[110px]" rowspan="2">Jam Tersedia</th>
                        <th class="py-3 px-3 text-right w-20" rowspan="2">Target</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="3">Qty Output Produksi</th>
                        <th class="py-3 px-3 text-center border-l border-slate-200 dark:border-slate-700 min-w-[90px]" rowspan="2">Kode Op</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="7">NG Subpart</th>
                        <th class="py-3 px-3 text-left border-l border-slate-200 dark:border-slate-700 min-w-[130px]" rowspan="2">Masalah</th>
                        <th class="py-3 px-3 text-left border-l border-slate-200 dark:border-slate-700 min-w-[130px]" rowspan="2">Tindakan</th>
                        <th class="py-2 px-3 text-center border-l border-slate-200 dark:border-slate-700" colspan="2">Checker</th>
                    </tr>
                    <tr class="bg-slate-50/70 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold">
                        <th class="py-1.5 px-3 text-right border-l border-slate-200 dark:border-slate-700 w-16 text-emerald-600 dark:text-emerald-400">OK</th>
                        <th class="py-1.5 px-3 text-right w-16 text-rose-600 dark:text-rose-400">NG PART</th>
                        <th class="py-1.5 px-3 text-right w-16">TOTAL</th>
                        <th class="py-1.5 px-2 text-center border-l border-slate-200 dark:border-slate-700 w-8">1</th>
                        <th class="py-1.5 px-2 text-center w-8">2</th>
                        <th class="py-1.5 px-2 text-center w-8">3</th>
                        <th class="py-1.5 px-2 text-center w-8">4</th>
                        <th class="py-1.5 px-2 text-center w-8">5</th>
                        <th class="py-1.5 px-2 text-center w-8">6</th>
                        <th class="py-1.5 px-2 text-center w-8">7</th>
                        <th class="py-1.5 px-3 text-center border-l border-slate-200 dark:border-slate-700 w-16">PRD</th>
                        <th class="py-1.5 px-3 text-center w-16">QC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                    $rowsShift1 = ['07.00 - 08.00', '08.00 - 09.00', '09.00 - 10.00', '10.00 - 11.00', '11.00 - 12.00', '12.00 - 13.00', '13.00 - 14.00', '14.00 - 15.00'];
                    $rowsShift2 = ['15.00 - 16.00', '16.00 - 17.00', '17.00 - 18.00', '18.00 - 19.00', '19.00 - 20.00', '20.00 - 21.00', '21.00 - 22.00', '22.00 - 23.00'];
                    $rowsShift3 = ['23.00 - 24.00', '24.00 - 01.00', '01.00 - 02.00', '02.00 - 03.00', '03.00 - 04.00', '04.00 - 05.00', '05.00 - 06.00', '06.00 - 07.00'];
                    @endphp

                    {{-- ==================== SHIFT 1 (PAGI) ==================== --}}
                    <tr class="bg-indigo-50/40 dark:bg-indigo-950/20">
                        <td colspan="18" class="py-2 px-4 font-bold text-xs text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">
                            <i class="fas fa-sun text-amber-500 mr-2"></i>Shift 1 (Pagi: 07.00 - 15.00)
                        </td>
                    </tr>
                    @foreach ($rowsShift1 as $lbl)
                    @php
                        $jamData = $produksiPerJamData['pagi'][$lbl] ?? null;
                        $ok = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                        $ng = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                        $opCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                        $tot = $ok + $ng;
                        $tgt = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-2.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">{{ $lbl }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-600 dark:text-slate-400">{{ $tgt > 0 ? number_format($tgt) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 border-l border-slate-100 dark:border-slate-800">{{ $ok > 0 ? number_format($ok) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ $ng > 0 ? number_format($ng) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 dark:text-white">{{ $tot > 0 ? number_format($tot) : '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $opCode ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono border-l border-slate-100 dark:border-slate-800 text-slate-500">{{ $jamData['ng_subpart_1'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_2'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_3'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_4'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_5'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_6'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_7'] ?? '' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['masalah'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['tindakan'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $jamData['checker_prd'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $jamData['checker_qc'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @php $subShift1 = ($totalPerShift['pagi']['ok'] ?? 0) + ($totalPerShift['pagi']['ng'] ?? 0); @endphp
                    <tr class="bg-indigo-50/80 dark:bg-indigo-950/40 font-bold border-t-2 border-indigo-200 dark:border-indigo-800">
                        <td class="py-3 px-3 font-black text-indigo-900 dark:text-indigo-200">SUBTOTAL SHIFT 1</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-indigo-900 dark:text-indigo-200">{{ number_format($totalPerShift['pagi']['target'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 border-l border-indigo-200 dark:border-indigo-800">{{ number_format($totalPerShift['pagi']['ok'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-rose-700 dark:text-rose-400">{{ number_format($totalPerShift['pagi']['ng'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-indigo-950 dark:text-white">{{ number_format($subShift1) }}</td>
                        <td colspan="13"></td>
                    </tr>

                    {{-- ==================== SHIFT 2 (SIANG) ==================== --}}
                    <tr class="bg-amber-50/40 dark:bg-amber-950/20">
                        <td colspan="18" class="py-2 px-4 font-bold text-xs text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                            <i class="fas fa-cloud-sun text-amber-500 mr-2"></i>Shift 2 (Siang: 15.00 - 23.00)
                        </td>
                    </tr>
                    @foreach ($rowsShift2 as $lbl)
                    @php
                        $jamData = $produksiPerJamData['siang'][$lbl] ?? null;
                        $ok = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                        $ng = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                        $opCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                        $tot = $ok + $ng;
                        $tgt = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-2.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">{{ $lbl }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-600 dark:text-slate-400">{{ $tgt > 0 ? number_format($tgt) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 border-l border-slate-100 dark:border-slate-800">{{ $ok > 0 ? number_format($ok) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ $ng > 0 ? number_format($ng) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 dark:text-white">{{ $tot > 0 ? number_format($tot) : '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $opCode ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono border-l border-slate-100 dark:border-slate-800 text-slate-500">{{ $jamData['ng_subpart_1'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_2'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_3'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_4'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_5'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_6'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_7'] ?? '' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['masalah'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['tindakan'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $jamData['checker_prd'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $jamData['checker_qc'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @php $subShift2 = ($totalPerShift['siang']['ok'] ?? 0) + ($totalPerShift['siang']['ng'] ?? 0); @endphp
                    <tr class="bg-amber-50/80 dark:bg-amber-950/40 font-bold border-t-2 border-amber-200 dark:border-amber-800">
                        <td class="py-3 px-3 font-black text-amber-900 dark:text-amber-200">SUBTOTAL SHIFT 2</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-amber-900 dark:text-amber-200">{{ number_format($totalPerShift['siang']['target'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 border-l border-amber-200 dark:border-amber-800">{{ number_format($totalPerShift['siang']['ok'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-rose-700 dark:text-rose-400">{{ number_format($totalPerShift['siang']['ng'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-slate-900 dark:text-white">{{ number_format($subShift2) }}</td>
                        <td colspan="13"></td>
                    </tr>

                    {{-- ==================== SHIFT 3 (MALAM) ==================== --}}
                    <tr class="bg-purple-50/40 dark:bg-purple-950/20">
                        <td colspan="18" class="py-2 px-4 font-bold text-xs text-purple-800 dark:text-purple-300 uppercase tracking-wider">
                            <i class="fas fa-moon text-indigo-400 mr-2"></i>Shift 3 (Malam: 23.00 - 07.00)
                        </td>
                    </tr>
                    @foreach ($rowsShift3 as $lbl)
                    @php
                        $jamData = $produksiPerJamData['malam'][$lbl] ?? null;
                        $ok = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                        $ng = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                        $opCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                        $tot = $ok + $ng;
                        $tgt = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-2.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">{{ $lbl }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-600 dark:text-slate-400">{{ $tgt > 0 ? number_format($tgt) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 border-l border-slate-100 dark:border-slate-800">{{ $ok > 0 ? number_format($ok) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ $ng > 0 ? number_format($ng) : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 dark:text-white">{{ $tot > 0 ? number_format($tot) : '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $opCode ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono border-l border-slate-100 dark:border-slate-800 text-slate-500">{{ $jamData['ng_subpart_1'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_2'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_3'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_4'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_5'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_6'] ?? '' }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $jamData['ng_subpart_7'] ?? '' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['masalah'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-600 dark:text-slate-400 border-l border-slate-100 dark:border-slate-800">{{ $jamData['tindakan'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800">{{ $jamData['checker_prd'] ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $jamData['checker_qc'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @php $subShift3 = ($totalPerShift['malam']['ok'] ?? 0) + ($totalPerShift['malam']['ng'] ?? 0); @endphp
                    <tr class="bg-purple-50/80 dark:bg-purple-950/40 font-bold border-t-2 border-purple-200 dark:border-purple-800">
                        <td class="py-3 px-3 font-black text-purple-900 dark:text-purple-200">SUBTOTAL SHIFT 3</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-purple-900 dark:text-purple-200">{{ number_format($totalPerShift['malam']['target'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 border-l border-purple-200 dark:border-purple-800">{{ number_format($totalPerShift['malam']['ok'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-rose-700 dark:text-rose-400">{{ number_format($totalPerShift['malam']['ng'] ?? 0) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-slate-900 dark:text-white">{{ number_format($subShift3) }}</td>
                        <td colspan="13"></td>
                    </tr>

                    {{-- ==================== GRAND TOTAL ROW ==================== --}}
                    @php
                    $grandTotalActual = ($grandTotal['ok'] ?? 0) + ($grandTotal['ng'] ?? 0);
                    @endphp
                    <tr class="bg-slate-900 text-white font-bold border-t-2 border-slate-950">
                        <td class="py-3.5 px-3 font-black uppercase tracking-wider text-amber-400">GRAND TOTAL</td>
                        <td class="py-3.5 px-3 text-right font-mono font-black text-white">{{ number_format($grandTotal['target'] ?? 0) }}</td>
                        <td class="py-3.5 px-3 text-right font-mono font-black text-emerald-400 border-l border-slate-700">{{ number_format($grandTotal['ok'] ?? 0) }}</td>
                        <td class="py-3.5 px-3 text-right font-mono font-black text-rose-400">{{ number_format($grandTotal['ng'] ?? 0) }}</td>
                        <td class="py-3.5 px-3 text-right font-mono font-black text-white">{{ number_format($grandTotalActual) }}</td>
                        <td colspan="13" class="text-xs text-slate-400 italic px-3 text-right">
                            Rekap akumulasi output 3 shift
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom Grid: NG Part Types & Digital Signatures --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Card: NG Part Types Breakdown --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 mb-0">
                            Rincian Jenis NG Part Types
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Klasifikasi defect per shift kerja</p>
                    </div>
                </div>
            </div>

            @if(isset($ngPartTypesData) && $ngPartTypesData['all_types'] && $ngPartTypesData['all_types']->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px] font-bold">
                            <th class="py-2.5 px-3">Nama NG Part</th>
                            <th class="py-2.5 px-2 text-center w-16">Shift 1</th>
                            <th class="py-2.5 px-2 text-center w-16">Shift 2</th>
                            <th class="py-2.5 px-2 text-center w-16">Shift 3</th>
                            <th class="py-2.5 px-2 text-center w-20 border-l border-slate-200 dark:border-slate-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php
                        $shift1Data = $ngPartTypesData['by_shift']['Shift 1'] ?? [];
                        $shift2Data = $ngPartTypesData['by_shift']['Shift 2'] ?? [];
                        $shift3Data = $ngPartTypesData['by_shift']['Shift 3'] ?? [];
                        @endphp

                        @foreach($ngPartTypesData['all_types'] as $ngPart)
                        @php
                            $s1 = 0; $s2 = 0; $s3 = 0;
                            foreach($shift1Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $s1 = $item['quantity'] ?? 0; break; } }
                            foreach($shift2Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $s2 = $item['quantity'] ?? 0; break; } }
                            foreach($shift3Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $s3 = $item['quantity'] ?? 0; break; } }
                            $totNg = $s1 + $s2 + $s3;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors {{ $totNg > 0 ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }}">
                            <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $ngPart->name }}
                                @if($ngPart->code)
                                <span class="text-[10px] font-mono text-slate-400 ml-1">({{ $ngPart->code }})</span>
                                @endif
                            </td>
                            <td class="py-2 px-2 text-center font-mono {{ $s1 > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $s1 > 0 ? $s1 : '-' }}</td>
                            <td class="py-2 px-2 text-center font-mono {{ $s2 > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $s2 > 0 ? $s2 : '-' }}</td>
                            <td class="py-2 px-2 text-center font-mono {{ $s3 > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $s3 > 0 ? $s3 : '-' }}</td>
                            <td class="py-2 px-2 text-center font-mono font-bold border-l border-slate-100 dark:border-slate-800 {{ $totNg > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $totNg > 0 ? $totNg : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-6 text-center text-slate-400 text-xs">
                Tidak ada data NG Part Types yang tercatat.
            </div>
            @endif
        </div>

        {{-- Card: Tanda Tangan & QR Verification --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 mb-0">
                        Verifikasi Digital & Tanda Tangan
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Validasi QR Code setiap shift</p>
                </div>
            </div>

            {{-- Shift Leaders Signature Cards --}}
            <div class="space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">
                    Shift Leader
                </span>

                @php
                $shiftLeaders = [
                    ['shift' => 'Shift 1', 'leader' => $leadersByShift['shift_1'] ?? null, 'qr' => $leadersQrByShift['shift_1'] ?? null],
                    ['shift' => 'Shift 2', 'leader' => $leadersByShift['shift_2'] ?? null, 'qr' => $leadersQrByShift['shift_2'] ?? null],
                    ['shift' => 'Shift 3', 'leader' => $leadersByShift['shift_3'] ?? null, 'qr' => $leadersQrByShift['shift_3'] ?? null],
                ];
                @endphp

                <div class="grid grid-cols-3 gap-2">
                    @foreach($shiftLeaders as $sl)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col items-center text-center">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                            {{ $sl['shift'] }}
                        </span>

                        @if($sl['qr'])
                        <img src="{!! $sl['qr'] !!}" alt="QR Leader" class="w-12 h-12 object-contain rounded-lg border border-slate-200 dark:border-slate-700 bg-white p-0.5 mb-2 shadow-xs">
                        @else
                        <div class="w-12 h-12 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-2">
                            <i class="fas fa-signature text-base"></i>
                        </div>
                        @endif

                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate w-full" title="{{ $sl['leader']->nama ?? '-' }}">
                            {{ $sl['leader']->nama ?? '-' }}
                        </span>
                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold uppercase mt-0.5">
                            {{ $sl['leader'] ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Operators Summary --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-2">
                    Operator Bertugas
                </span>

                @php
                $shiftsOp = [
                    'Shift 1' => $operatorsByShift['shift_1'] ?? [],
                    'Shift 2' => $operatorsByShift['shift_2'] ?? [],
                    'Shift 3' => $operatorsByShift['shift_3'] ?? [],
                ];
                @endphp

                <div class="space-y-2">
                    @foreach($shiftsOp as $shiftName => $ops)
                    <div class="text-xs">
                        <span class="font-bold text-slate-600 dark:text-slate-400 text-[11px] mr-1.5">{{ $shiftName }}:</span>
                        @if(count($ops) > 0)
                            @foreach($ops as $op)
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 mr-1 mb-1">
                                {{ $op['nama'] ?? '-' }}
                            </span>
                            @endforeach
                        @else
                            <span class="text-slate-400 text-[11px]">-</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- SOP & 5R Compliance Notes Banner --}}
    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-4 sm:p-5 text-amber-900 dark:text-amber-200 shadow-sm flex items-start gap-3.5">
        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="space-y-1 text-xs">
            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 mb-0.5">
                Standar Operasional Prosedur (SOP) & Kepatuhan 5R
            </h4>
            <p class="mb-0 text-amber-800/90 dark:text-amber-300/90">
                <strong>* Pergantian Material:</strong> Apabila terdapat pergantian material produksi, wajib memastikan proses cleaning hopper telah terlaksana secara menyeluruh dan terdokumentasi.
            </p>
            <p class="mb-0 text-amber-800/90 dark:text-amber-300/90">
                <strong>** Audit 5R Mesin:</strong> Biji plastik tidak boleh berserakan di area mesin/meja, bersihkan debu pada bodi mesin, serta periksa pelumasan/grease pada sela dan roda pintu pengaman.
            </p>
        </div>
    </div>

</div>
@endsection