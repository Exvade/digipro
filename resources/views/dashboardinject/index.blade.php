@extends('layouts.app')

@section('title', 'Dashboard Inject')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-inject.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-inject-oee.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-inject-tooltip.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-inject-responsive-custom.css') }}">
@endpush

@section('content')
@auth
@if((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-2"></i>
    {{ session('kabag_approved') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@endauth

<!-- Info boxes Grid System -->
<div class="row info-boxes-row mb-3 mt-3">
    <!-- Total Mesin -->
    <div class="col-6 col-md-4 col-lg-custom-5 mb-2">
        <div class="info-box-modern status-total h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern text-truncate">{{ $totalMesins }}</div>
                <div class="info-box-text-modern">TOTAL</div>
                <div class="info-box-subtitle-modern">Mesin</div>
            </div>
        </div>
    </div>

    <!-- Running -->
    <div class="col-6 col-md-4 col-lg-custom-5 mb-2">
        <div class="info-box-modern status-running h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern text-truncate">{{ $countAktif }}</div>
                <div class="info-box-text-modern">RUNNING</div>
                <div class="info-box-subtitle-modern">Normal</div>
                <div class="mt-2 aggregate-hours-container" style="border-top: 1px solid rgba(128,128,128,0.1); padding-top: 5px;">
                    <i class="far fa-clock mr-1 text-primary"></i> <span id="agg-running-jam" class="aggregate-hours-value">{{ $aggregates['running'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stop -->
    <div class="col-6 col-md-4 col-lg-custom-5 mb-2">
        <div class="info-box-modern status-stop h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-stop-circle"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern text-truncate">{{ $countStop }}</div>
                <div class="info-box-text-modern">STAND BY</div>
                <div class="info-box-subtitle-modern">Planned Stop</div>
                <div class="mt-2 aggregate-hours-container" style="border-top: 1px solid rgba(128,128,128,0.1); padding-top: 5px;">
                    <i class="far fa-clock mr-1 text-warning"></i> <span id="agg-standby-jam" class="aggregate-hours-value">{{ $aggregates['standby'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance -->
    <div class="col-6 col-md-4 col-lg-custom-5 mb-2">
        <div class="info-box-modern status-maintenance h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-tools"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern text-truncate">{{ $countMaintenance }}</div>
                <div class="info-box-text-modern">DOWNTIME</div>
                <div class="info-box-subtitle-modern">Breakdown</div>
                <div class="mt-2 aggregate-hours-container" style="border-top: 1px solid rgba(128,128,128,0.1); padding-top: 5px;">
                    <i class="far fa-clock mr-1 text-danger"></i> <span id="agg-downtime-jam" class="aggregate-hours-value">{{ $aggregates['downtime'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Offline -->
    <div class="col-6 col-md-4 col-lg-custom-5 mb-2">
        <div class="info-box-modern status-offline h-100">
            <div class="info-box-icon-modern">
                <i class="fas fa-power-off"></i>
            </div>
            <div class="info-box-content-modern">
                <div class="info-box-number-modern text-truncate">{{ $countTidakAktif }}</div>
                <div class="info-box-text-modern">OFF</div>
                <div class="info-box-subtitle-modern">No Plan</div>
                <div class="mt-2 aggregate-hours-container" style="border-top: 1px solid rgba(128,128,128,0.1); padding-top: 5px;">
                    <i class="far fa-clock mr-1 text-muted"></i> <span id="agg-offline-jam" class="aggregate-hours-value">{{ $aggregates['offline'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <!-- Kolom daftar mesin -->
    <div class="col-12 mb-1">
        <!-- Daftar Mesin -->
        <div class="card mb-0 shadow-sm border-0">
            <div class="card-header dashboard-card-header d-flex flex-column flex-md-row justify-content-between align-items-center py-2 bg-transparent border-bottom">
                <h3 class="card-title m-0 text-center font-weight-bold" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                    DAFTAR MESIN PRODUKSI
                </h3>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <div class="machine-filter-container d-flex align-items-center bg-dark-soft p-1 rounded-pill">
                        <span class="small text-muted mx-2 d-none d-lg-inline">Tampilkan:</span>
                        <button class="filter-btn" data-limit="8">8</button>
                        <button class="filter-btn" data-limit="16">16</button>
                        <button class="filter-btn active" data-limit="all">Semua</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="machine-grid-wrapper">
                    <div class="row machine-grid" id="mesinGrid">
                        @foreach($machines as $mesin)
                        @include('dashboardinject.partials.machine-card-simple', ['mesin' => $mesin, 'machineProductionData' => $machineProductionData])
                        @endforeach
                    </div>
                </div>

                {{-- Pagination Controls --}}
                <div id="paginationControls" class="d-none justify-content-center align-items-center mt-3 mb-2">
                    <button id="prevPage" class="btn btn-sm btn-outline-primary rounded-circle mx-2">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator" class="small font-weight-bold mx-2">
                        Halaman <span id="currentPageNum">1</span> dari <span id="totalPageNum">1</span>
                    </div>
                    <button id="nextPage" class="btn btn-sm btn-outline-primary rounded-circle mx-2">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Edit Mesin Modal -->
<div class="modal fade" id="editMesinModal" tabindex="-1" aria-labelledby="editMesinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editMesinModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Mesin
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editMesinModalBody">
                <!-- Form will be loaded here -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data mesin...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('dashboardinject.partials.modal-detail-mesin')
@endsection

@push('scripts')
<div id="dashboard-data" style="display: none;"
    data-is-authenticated="{{ Auth::check() ? 'true' : 'false' }}"
    data-login-route="{{ route('login') }}"
    data-docs-url="{{ route('dashboard.inject.machine.documents', ['id' => '__ID__']) }}"
    data-machines="{{ json_encode($machines) }}"
    data-production-data="{{ json_encode($machineProductionData) }}"
    @auth
    @if((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved'))
    data-kabag-approved="{{ json_encode(session('kabag_approved')) }}"
    @endif
    @endauth
    ></div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dashboard Inject JS -->
<script src="{{ asset('js/dashboard-inject.js') }}?v={{ time() }}"></script>
<!-- Dashboard Inject Realtime Update -->
<script src="{{ asset('js/dashboard-inject-realtime.js') }}?v={{ time() }}"></script>
<!-- Dashboard Inject Main Logic -->
<script src="{{ asset('js/dashboard-inject-main.js') }}?v={{ time() }}"></script>




<!-- Dashboard Inject Pagination & Tooltips -->
<script src="{{ asset('js/dashboard-inject-pagination.js') }}?v={{ time() }}"></script>

<!-- Dashboard Inject Edit Modal Handler -->
<script src="{{ asset('js/dashboard-inject-edit-modal.js') }}?v={{ time() }}"></script>
@endpush