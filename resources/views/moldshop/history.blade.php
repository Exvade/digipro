@extends('moldshop.app')

@section('title', 'Riwayat Mold - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Moldshop CSS -->
<link rel="stylesheet" href="{{ asset('css/moldshop.css') }}?v={{ filemtime(public_path('css/moldshop.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 flex items-center justify-center text-sky-600 dark:text-sky-400 shadow-sm flex-shrink-0">
            <i class="fas fa-history text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                Riwayat Penggunaan Mold
            </h1>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                Statistik akumulasi produksi, total shoot, OK & NG setiap mold
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('moldshop.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Data Mold
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title">
                    <i class="fas fa-chart-line text-sky-500 mr-2"></i>
                    Data Akumulasi Produksi Mold
                </h3>
            </div>
            <div class="card-body">
                <table id="historyTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>No Mold</th>
                                <th>Nama Mold</th>
                                <th>Total OK</th>
                                <th>Total NG</th>
                                <th class="bg-primary text-white">Total Shoot</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($moldshops as $index => $mold)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $mold->no_mold }}</div>
                                </td>
                                <td>{{ $mold->nama_mold }}</td>
                                <td class="text-center text-success font-weight-bold">
                                    {{ number_format($mold->total_ok ?? 0) }}
                                </td>
                                <td class="text-center text-danger font-weight-bold">
                                    {{ number_format($mold->total_ng ?? 0) }}
                                </td>
                                <td class="text-center font-weight-bold" style="background-color: rgba(0, 123, 255, 0.1);">
                                    {{ number_format($mold->total_shoot) }}
                                </td>
                                <td class="text-center">
                                    @if ($mold->status == 'OK')
                                    <span class="badge badge-success">OK</span>
                                    @elseif ($mold->status == 'REPAIR')
                                    <span class="badge badge-danger">REPAIR</span>
                                    @elseif ($mold->status == 'PREVENTIVE')
                                    <span class="badge badge-warning">PREVENTIVE</span>
                                    @else
                                    <span class="badge badge-secondary">{{ ucfirst($mold->status ?? '-') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info viewUsageBtn" data-id="{{ $mold->id }}" data-no="{{ $mold->no_mold }}">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Penggunaan --}}
<div class="modal fade" id="modalUsage" tabindex="-1" role="dialog" aria-labelledby="modalUsageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalUsageLabel">
                    <i class="fas fa-info-circle mr-1"></i> Detail Penggunaan Mold: <span id="detailNoMold"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th>Tanggal</th>
                                <th>Nama Part</th>
                                <th>Output OK</th>
                                <th>Output NG</th>
                                <th class="bg-primary text-white">Total Shoot</th>
                            </tr>
                        </thead>
                        <tbody id="usageTableBody">
                            {{-- Content filled by AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
@endpush