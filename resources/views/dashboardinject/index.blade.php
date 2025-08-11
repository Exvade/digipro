@extends('layouts.app')

@section('title', 'Dashboard Inject')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-inject.css') }}">
@endpush

@section('content')
@if((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('kabag_approved') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="form-group">
            <!-- HAPUS DROPDOWN GRUP DARI SINI -->
        </div>
    </div>
    <div class="col-md-8 d-flex align-items-end">
        <!-- Hapus peringatan pilih grup -->
    </div>
</div>

<!-- Info boxes -->
<div class="info-boxes-horizontal mb-4">
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-info">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $totalMesins }}</div>
            <div class="info-box-text-modern">Total Mesin</div>
            <div class="info-box-subtitle-modern">PT Mada Wikri Tunggal Plant 3</div>
        </div>
    </div>
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $countAktif }}</div>
            <div class="info-box-text-modern">Mesin Aktif</div>
            <div class="info-box-subtitle-modern">Beroperasi Normal</div>
        </div>
    </div>
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-warning">
            <i class="fas fa-tools"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $countMaintenance }}</div>
            <div class="info-box-text-modern">Maintenance</div>
            <div class="info-box-subtitle-modern">Dalam Perbaikan</div>
        </div>
    </div>
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-danger">
            <i class="fas fa-power-off"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $countTidakAktif }}</div>
            <div class="info-box-text-modern">Tidak Aktif</div>
            <div class="info-box-subtitle-modern">Offline</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mb-4">
        <!-- Daftar Mesin -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    <span class="d-none d-sm-inline">Daftar Mesin Produksi</span>
                    <span class="d-sm-none">Mesin Produksi</span>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary d-none d-sm-inline">Total: {{ $totalMesins }} Mesin</span>
                    <span class="badge badge-primary d-sm-none">{{ $totalMesins }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="mesinGrid">
                    @foreach($machines as $mesin)
                    @php
                        $statusClass = 'bg-success';
                        $statusIcon = 'check-circle';
                        $statusText = 'Aktif';
                        if ($mesin->status === 'maintenance') {
                            $statusClass = 'bg-warning';
                            $statusIcon = 'tools';
                            $statusText = 'Maintenance';
                        } elseif ($mesin->status === 'tidak aktif') {
                            $statusClass = 'bg-danger';
                            $statusIcon = 'power-off';
                            $statusText = 'Offline';
                        }
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3 mesin-item" data-mesin-id="{{ $mesin->id }}" data-mesin-nama="{{ $mesin->nama_mesin }}" data-mesin-brand="{{ $mesin->brand_mesin }}" data-mesin-tonase="{{ $mesin->tonase_mesin }}" data-mesin-status="{{ $mesin->status }}">
                        <div class="card machine-card machine-card-3d h-100">
                            <div class="card-header p-2 border-0 bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ $statusClass }} text-white">{{ $statusText }}</span>
                                    <span class="badge badge-secondary">#{{ $loop->iteration }}</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="text-center mb-3">
                                    <i class="fas fa-cogs fa-2x text-primary mb-2"></i>
                                    <h5 class="card-title text-truncate mb-0">{{ $mesin->nama_mesin }}</h5>
                                </div>
                                <div class="machine-details">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-tag mr-2 text-info"></i>
                                        <small>Brand: <strong>{{ $mesin->brand_mesin }}</strong></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-weight-hanging mr-2 text-success"></i>
                                        <small>Tonase: <strong>{{ $mesin->tonase_mesin }} Ton</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-12">
        @include('dashboardinject.partials.statistik-chart')
        @include('dashboardinject.partials.quick-stats')
    </div>
</div>

@include('dashboardinject.partials.modal-detail-mesin')
@endsection

@push('scripts')
@if((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved'))
<script>
window.kabagApproved = @json(session('kabag_approved'));
</script>
@endif
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Bootstrap JS (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dashboard Inject JS -->
<script src="{{ asset('js/dashboard-inject.js') }}"></script>
@endpush