@extends('laporan-produksi.app')

@section('title', 'Laporan Produksi - Digital Production System')

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Laporan Produksi
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Alert Messages --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="icon fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

@if(request('tanggal_awal') && request('tanggal_akhir'))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="icon fas fa-info-circle mr-2"></i>
    Menampilkan data periode:
    <strong>{{ \Carbon\Carbon::parse(request('tanggal_awal'))->format('d-m-Y') }}</strong>
    s/d
    <strong>{{ \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d-m-Y') }}</strong>
    <span class="badge badge-primary ml-2">{{ $dataProduksi->count() }} data</span>
</div>
@endif

{{-- Tab Filter Section -- Hanya tampilkan jika bukan leader dengan bagian spesifik --}}
@php
$user = Auth::user();
$isLeaderInject = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject');
$isLeaderAssy = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy');
@endphp

@if(!$isLeaderInject && !$isLeaderAssy)
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsLaporan" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab-laporan" href="#" data-bagian="all">
                            <i class="fas fa-list"></i> All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inject-tab-laporan" href="#" data-bagian="inject">
                            <i class="fas fa-cog"></i> Inject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assy-tab-laporan" href="#" data-bagian="assy">
                            <i class="fas fa-tools"></i> Assy
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Filter Card --}}
<div class="card card-outline card-info filter-card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-2"></i>Filter & Aksi
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('laporan-produksi.index') }}" class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold">Filter Grup</label>
                    <select class="form-control" name="grup_filter" id="grup_filter">
                        <option value="">Semua Grup</option>
                        @foreach ($grups as $grup)
                        <option value="{{ $grup->id }}"
                            {{ request('grup_filter') == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold">Filter Mesin</label>
                    <select class="form-control" name="mesin_filter" id="mesin_filter">
                        <option value="">Semua Mesin</option>
                        @foreach ($mesins as $mesin)
                        <option value="{{ $mesin->id }}"
                            {{ request('mesin_filter') == $mesin->id ? 'selected' : '' }}>
                            {{ $mesin->nama_mesin }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group mb-0">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i>Filter
                        </button>
                        <a href="{{ route('laporan-produksi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo mr-1"></i>Reset
                        </a>
                        @if(request('tanggal_awal') && request('tanggal_akhir'))
                        <a href="{{ route('laporan-produksi.cetak', [
                                    'tanggal_awal' => request('tanggal_awal'),
                                    'tanggal_akhir' => request('tanggal_akhir'),
                                    'grup_filter' => request('grup_filter'),
                                    'mesin_filter' => request('mesin_filter')
                                ]) }}"
                            class="btn btn-success" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>Cetak PDF
                        </a>
                        @else
                        <a href="{{ route('laporan-produksi.cetak', [
                                    'grup_filter' => request('grup_filter'),
                                    'mesin_filter' => request('mesin_filter')
                                ]) }}"
                            class="btn btn-success" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>Cetak Semua
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Data Laporan Produksi
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableLaporanProduksi"
                class="table table-striped table-bordered table-hover align-middle"
                style="width:100%"
                data-leader-inject="{{ $isLeaderInject ? 'true' : 'false' }}"
                data-leader-assy="{{ $isLeaderAssy ? 'true' : 'false' }}">
                <thead class="text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Tanggal</th>
                        <th width="8%">Bagian</th>
                        <th width="15%">Mesin/Meja</th>
                        <th width="12%">Operator</th>
                        <th width="10%">Qty Planning</th>
                        <th width="10%">OK Count</th>
                        <th width="10%">NG Count</th>
                        <th width="10%">Total</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataProduksi as $index => $data)
                    <tr data-bagian="{{ $data->planning->bagian ?? 'inject' }}">
                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                        <td>{{ $data->created_at->format('d-m-Y H:i') }}</td>
                        <td class="text-center">
                            @if(($data->planning->bagian ?? 'inject') === 'assy')
                            <span class="badge badge-info">Assy</span>
                            @else
                            <span class="badge badge-primary">Inject</span>
                            @endif
                        </td>
                        <td class="font-weight-bold">
                            @if(($data->planning->bagian ?? 'inject') === 'assy')
                            {{ $data->planning->meja->nama_meja ?? '-' }}
                            @else
                            {{ $data->planning->mesin->nama_mesin ?? '-' }}
                            @endif
                        </td>
                        <td>
                            @if($data->planning->plotings->count() > 0)
                            @foreach($data->planning->plotings as $ploting)
                            <span class="badge badge-info mb-1">
                                {{ $ploting->operator->nama ?? '-' }}
                            </span>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($data->planning->qty_planning_produksi ?? 0) }}</td>
                        <td class="text-right">
                            <span class="badge badge-success">{{ number_format($data->ok_count) }}</span>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-danger">{{ number_format($data->ng_count) }}</span>
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format($data->ok_count + $data->ng_count) }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button"
                                    class="btn btn-sm btn-info btn-view-detail btn-action"
                                    data-id="{{ $data->id }}"
                                    title="Detail"
                                    data-toggle="tooltip">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('laporan-produksi.cetak-single', $data->id) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-primary btn-action"
                                    title="Cetak Detail"
                                    data-toggle="tooltip">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    {{-- Empty state will be handled by DataTables --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle text-primary mr-2"></i>Detail Laporan Produksi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Tutup
                </button>
                <a href="#" id="btnCetakModal" target="_blank" class="btn btn-primary">
                    <i class="fas fa-print mr-2"></i>Cetak Laporan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
{{-- Planning CSS for Tab Filter --}}
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
{{-- Custom CSS --}}
<link rel="stylesheet" href="{{ asset('css/laporan-produksi-index.css') }}">
@endpush

@push('scripts')
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
{{-- Custom JS --}}
<script src="{{ asset('js/laporan-produksi-index.js') }}"></script>

{{-- Tab Filter Script --}}
<script>
    $(document).ready(function() {
        // Hanya jalankan jika tab filter ada (bukan leader dengan bagian spesifik)
        if ($('#bagianTabsLaporan').length > 0) {
            // Tab filter untuk Laporan Produksi
            $('#bagianTabsLaporan .nav-link').on('click', function(e) {
                e.preventDefault();
                const bagian = $(this).data('bagian');

                // Update active tab
                $('#bagianTabsLaporan .nav-link').removeClass('active');
                $(this).addClass('active');

                // Filter DataTable
                const table = $('#tableLaporanProduksi').DataTable();
                if (bagian === 'all') {
                    table.column(2).search('').draw(); // Column 2 = Bagian
                } else {
                    table.column(2).search(bagian, false, false).draw();
                }
            });
        } else {
            // Untuk leader dengan bagian spesifik, filter otomatis saat DataTable ready
            const tableEl = $('#tableLaporanProduksi');
            const isLeaderInject = tableEl.data('leader-inject');
            const isLeaderAssy = tableEl.data('leader-assy');

            if (isLeaderInject) {
                // Leader inject - filter ke inject
                $(document).on('init.dt', '#tableLaporanProduksi', function() {
                    const table = $('#tableLaporanProduksi').DataTable();
                    table.column(2).search('inject', false, false).draw();
                });
            } else if (isLeaderAssy) {
                // Leader assy - filter ke assy
                $(document).on('init.dt', '#tableLaporanProduksi', function() {
                    const table = $('#tableLaporanProduksi').DataTable();
                    table.column(2).search('assy', false, false).draw();
                });
            }
        }
    });
</script>
@endpush