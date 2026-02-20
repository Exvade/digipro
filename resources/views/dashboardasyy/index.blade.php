@extends('layouts.app')

@section('title', 'Dashboard Assy')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-assy.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-assy-dark.css') }}">
@endpush

@section('content')
<!-- Info boxes Grid System -->
<div class="row mb-4 mt-3">
    <!-- Total Meja -->
    <div class="col-6 col-md-4">
        <div class="info-box-modern status-total h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-table"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern">{{ $totalMejas }}</div>
                <div class="info-box-text-modern">TOTAL</div>
                <div class="info-box-subtitle-modern">Meja Assy</div>
            </div>
        </div>
    </div>

    <!-- Aktif -->
    <div class="col-6 col-md-4">
        <div class="info-box-modern status-running h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern">{{ $countAktif }}</div>
                <div class="info-box-text-modern">AKTIF</div>
                <div class="info-box-subtitle-modern">Normal</div>
            </div>
        </div>
    </div>



    <!-- Offline -->
    <div class="col-6 col-md-4">
        <div class="info-box-modern status-offline h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-power-off"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern">{{ $countTidakAktif }}</div>
                <div class="info-box-text-modern">OFFLINE</div>
                <div class="info-box-subtitle-modern">Mati</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <!-- Daftar Meja -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2 bg-transparent border-bottom">
                <div style="width: 40px;" class="d-none d-md-block"></div>
                <h3 class="card-title m-0 text-center flex-grow-1 font-weight-bold" style="font-size: 1.1rem; letter-spacing: 0.5px; text-transform: uppercase;">
                    Daftar Meja Assembly
                </h3>
                <div style="width: 40px;"></div>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-2" id="mejaGrid">
                    @foreach ($mejas as $meja)
                    @php
                    $prodData = $mejaProductionData[$meja->id_meja] ?? null;
                    $hasPlanning = $prodData && $prodData['has_planning'];
                    @endphp
                    <div class="col mb-3 meja-item" data-status="{{ $meja->status }}">
                        <div class="card meja-card-simple h-100 {{ $meja->status }}"
                            data-meja-id="{{ $meja->id_meja }}"
                            data-meja-nama="{{ $meja->nama_meja }}"
                            data-meja-status="{{ $meja->status }}">

                            <div class="card-content-wrapper">
                                <span class="machine-name-simple">{{ $meja->nama_meja }}</span>

                                <div class="machine-info-content">
                                    @if($hasPlanning)
                                    <div class="machine-part-simple" title="{{ $prodData['part_name'] }}">
                                        {{ $prodData['part_name'] }}
                                    </div>
                                    <div class="machine-details-simple">
                                        <i class="fas fa-user-tag mr-1"></i>{{ $prodData['customer'] }}
                                    </div>
                                    @else
                                    <div class="empty-state-mini">
                                        <i class="fas fa-clipboard-list"></i>
                                        <span>No Planning</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="machine-status-display">
                                    {{ $meja->status === 'aktif' ? 'AKTIF' : ($meja->status === 'maintenance' ? 'MAINTENANCE' : 'OFFLINE') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


</div>

@include('dashboardasyy.partials.modals')

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">NOTIFICATIONS</h5>
            </div>
            <div class="modal-body">
                Ada checksheet yang telah di-approve oleh Kabag.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Bootstrap JS (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dashboard Assy JS -->
<script src="{{ asset('js/dashboard-assy.js') }}"
    data-count-aktif="{{ $countAktif }}"
    data-count-maintenance="{{ $countMaintenance }}"
    data-count-tidak-aktif="{{ $countTidakAktif }}"
    data-checksheet-store-url="{{ route('dashboard.assy.checksheet.store') }}"
    data-meja-documents-url="{{ route('dashboard.assy.meja.documents', ['id' => '__ID__']) }}"
    data-kabag-approved="{{ session('kabag_approved') ? 'true' : 'false' }}">
</script>
<script src="{{ asset('js/dashboard-assy-init.js') }}"></script>
@endpush