@extends('layouts.app')

@section('title', 'Approval Planning Produksi - Digital Production System')

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/25 flex-shrink-0">
            <i class="fas fa-check-double"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Approval Planning Produksi
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>{{ $plannings->total() }} Menunggu Approval</span>
                </span>
                @if($filterTanggal)
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60 inline-flex items-center gap-1">
                    <i class="far fa-calendar text-[10px]"></i>
                    <span>{{ \Carbon\Carbon::parse($filterTanggal)->translatedFormat('d M Y') }}</span>
                </span>
                @else
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 inline-flex items-center gap-1">
                    <i class="fas fa-layer-group text-[10px]"></i>
                    <span>Semua Tanggal</span>
                </span>
                @endif
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Verifikasi dan otorisasi jadwal produksi oleh Kepala Bagian PPIC
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
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

    {{-- Filter Bar & Navigation Tabs --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Bagian Tab Pills --}}
        <div>
            <ul class="bagian-tabs flex items-center gap-1.5 list-none p-0 m-0" id="bagianTabsApproval" role="tablist">
                <li class="nav-item m-0">
                    <a class="nav-link active inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all" id="inject-tab-approval" href="#" data-bagian="inject">
                        <i class="fas fa-cog"></i>
                        <span>Bagian Inject</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Filter Tanggal Controls --}}
        <form method="GET" action="{{ route('planning.approval.page') }}" class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5 mr-1">
                <i class="far fa-calendar-alt text-xs"></i>
                <span>Tanggal Rencana:</span>
            </span>
            <input type="date" 
                class="filter-date-input px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none dark:[color-scheme:dark]" 
                style="color-scheme: light dark;"
                id="tanggal" 
                name="tanggal"
                value="{{ $filterTanggal ?? '' }}">
            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                <i class="fas fa-search text-xs"></i>
                <span>Filter</span>
            </button>
            <a href="{{ route('planning.approval.page') }}" 
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold {{ empty($filterTanggal) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200 dark:border-slate-700' }} transition-all"
                title="Tampilkan semua pending planning">
                <i class="fas fa-layer-group text-[10px]"></i>
                <span>Semua</span>
            </a>
            <a href="{{ route('planning.approval.page', ['tanggal' => \Carbon\Carbon::tomorrow()->format('Y-m-d')]) }}" 
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold {{ ($filterTanggal === \Carbon\Carbon::tomorrow()->format('Y-m-d')) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200 dark:border-slate-700' }} transition-all"
                title="Filter rencana besok">
                <span>Besok</span>
            </a>
            <a href="{{ route('planning.approval.page', ['tanggal' => now()->format('Y-m-d')]) }}" 
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold {{ ($filterTanggal === now()->format('Y-m-d')) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200 dark:border-slate-700' }} transition-all"
                title="Filter rencana hari ini">
                <span>Hari Ini</span>
            </a>
        </form>
    </div>

    {{-- Main Approval Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-emerald-500"></i>
                    <span>Daftar Planning Menunggu Approval</span>
                </h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                    Gunakan centang masal untuk menyetujui beberapa planning sekaligus
                </p>
            </div>

            {{-- Bulk Action Button Container --}}
            <div id="bulkActionGroup" style="display: none;">
                <button type="button" 
                    id="btnBulkApprove" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30 transition-all cursor-pointer animate-bounce-subtle">
                    <i class="fas fa-check-double text-xs"></i>
                    <span>Approve Terpilih (<span id="selectedCount">0</span>)</span>
                </button>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar p-6">
            @if($plannings->count() > 0)
            <table id="approvalTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/60">
                        <th class="py-3 px-3 text-center rounded-l-xl" style="width: 40px;">
                            <input type="checkbox" id="checkAll" class="planning-checkbox">
                        </th>
                        <th class="py-3 px-3 text-center" style="width: 50px;">No</th>
                        <th class="py-3 px-4">No Part</th>
                        <th class="py-3 px-4">Nama Part</th>
                        <th class="py-3 px-4 text-center">Bagian</th>
                        <th class="py-3 px-4 text-center">Mesin</th>
                        <th class="py-3 px-4 text-center">Tanggal</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Qty Planning</th>
                        <th class="py-3 px-4 text-center rounded-r-xl" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                    @foreach ($plannings as $planning)
                    <tr id="row-{{ $planning->id }}" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-3 text-center">
                            <input type="checkbox" 
                                class="planning-checkbox" 
                                id="check-{{ $planning->id }}" 
                                value="{{ $planning->id }}">
                        </td>
                        <td class="py-3.5 px-3 text-center font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                            {{ $plannings->firstItem() + $loop->index }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 dark:text-white font-mono text-xs">
                                {{ $planning->part->no_parts ?? '-' }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs">
                                {{ $planning->part->nama_part ?? '-' }}
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
                        <td class="py-3.5 px-4 text-center font-mono text-xs font-medium text-slate-600 dark:text-slate-300">
                            {{ \Carbon\Carbon::parse($planning->tanggal)->format('d-m-Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-mono font-black text-xs text-indigo-600 dark:text-indigo-400">
                                {{ number_format($planning->qty_planning_produksi) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block font-medium">Pcs</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                <button type="button" 
                                    class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 dark:hover:text-white border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center transition-all approveBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                    data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                    data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                    title="Setujui Planning">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white border border-rose-200/80 dark:border-rose-800/60 flex items-center justify-center transition-all rejectBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    title="Tolak Planning">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white dark:hover:bg-sky-600 dark:hover:text-white border border-sky-200/80 dark:border-sky-800/60 flex items-center justify-center transition-all viewBtn cursor-pointer"
                                    data-id="{{ $planning->id }}"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-12 px-4">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Semua Planning Sudah Di-Review</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-0">Tidak ada planning yang berstatus pending untuk tanggal yang dipilih.</p>
            </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($plannings->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex justify-center">
            {{ $plannings->appends(['tanggal' => $filterTanggal])->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Modal Approval --}}
<div class="modal fade" id="modalApproval" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between" id="approvalModalHeader">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="modalApprovalLabel">
                            Approve Planning
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Konfirmasi persetujuan rencana produksi
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <form id="formApproval" method="POST" data-action-url="{{ route('planning.approval') }}">
                    @csrf
                    <input type="hidden" id="approval_planning_id" name="planning_id">
                    <input type="hidden" id="approval_action" name="action" value="approve">

                    <div id="approveContent" class="space-y-4">
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 text-xs leading-relaxed">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-emerald-600 mt-0.5 text-base"></i>
                                <div>
                                    <strong class="font-bold text-sm block mb-1">Konfirmasi Persetujuan</strong>
                                    Apakah Anda yakin ingin menyetujui planning produksi ini? QR Code approval resmi akan langsung dibuat dengan identitas Anda sebagai approver.
                                </div>
                            </div>
                        </div>

                        {{-- Quick Detail Card --}}
                        <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-2">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Ringkasan Planning</div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-200/50 dark:border-slate-700/50">
                                <span class="text-slate-500 dark:text-slate-400">No Part:</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200" id="approval-no_part">-</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-200/50 dark:border-slate-700/50">
                                <span class="text-slate-500 dark:text-slate-400">Nama Part:</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" id="approval-nama_part">-</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1">
                                <span class="text-slate-500 dark:text-slate-400">Qty Target:</span>
                                <span class="font-mono font-black text-indigo-600 dark:text-indigo-400" id="approval-qty">-</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer" id="submitApprovalBtn">
                    <i class="fas fa-check mr-1.5"></i> Konfirmasi Approve
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0">
                            Reject Planning
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Tolak rencana produksi dengan memberikan alasan
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <form id="formReject" method="POST" data-action-url="{{ route('planning.approval') }}">
                    @csrf
                    <input type="hidden" id="reject_planning_id" name="planning_id">
                    <input type="hidden" name="action" value="reject">

                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-xs">
                        <div class="flex items-start gap-2.5">
                            <i class="fas fa-exclamation-triangle text-rose-600 mt-0.5 text-sm"></i>
                            <div>
                                <strong class="font-bold block mb-1">Perhatian</strong>
                                Planning yang ditolak akan dikembalikan ke pembuat rencana dengan status REJECTED beserta alasan penolakan.
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
                            rows="4"
                            placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                        <small class="text-xs text-slate-400 mt-1 block">Wajib diisi agar tim produksi mengetahui bagian yang perlu direvisi</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-all cursor-pointer" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition-all cursor-pointer" id="btnConfirmReject">
                    <i class="fas fa-times mr-1.5"></i> Konfirmasi Reject
                </button>
            </div>
        </div>
    </div>
</div>

@include('planning.show')

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