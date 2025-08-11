@extends('layouts.app')

@section('title', 'Dashboard Assy')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-assy.css') }}">
@endpush

@section('content')
@if((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Approved!',
    text: '{{ session('kabag_approved') }}',
    timer: 4000,
    showConfirmButton: false
});
</script>
@endif

<!-- Info boxes -->
<div class="info-boxes-horizontal mb-4">
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-info">
            <i class="fas fa-table"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $totalMejas }}</div>
            <div class="info-box-text-modern">Total Meja</div>
            <div class="info-box-subtitle-modern">PT Mada Wikri Tunggal Plant 3</div>
        </div>
    </div>
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="info-box-content-modern">
            <div class="info-box-number-modern">{{ $countAktif }}</div>
            <div class="info-box-text-modern">Meja Aktif</div>
            <div class="info-box-subtitle-modern">Beroperasi Normal</div>
        </div>
    </div>
    <div class="info-box-modern">
        <div class="info-box-icon-modern bg-danger">
            <i class="fas fa-stop-circle"></i>
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
        <!-- Daftar Meja -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>
                    <span class="d-none d-sm-inline">Daftar Meja Assembly</span>
                    <span class="d-sm-none">Meja Assembly</span>
                </h3>
                <div class="card-tools">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="filterMeja('all')">Semua</button>
                        <button type="button" class="btn btn-outline-success" onclick="filterMeja('aktif')">Aktif</button>
                        <button type="button" class="btn btn-outline-danger" onclick="filterMeja('tidak_aktif')">Tidak Aktif</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="mejaGrid">
                    @foreach($mejas as $meja)
                    @php
                        $statusClass = 'bg-success';
                        $statusIcon = 'check-circle';
                        $statusText = 'Aktif';
                        if ($meja->status === 'tidak_aktif') {
                            $statusClass = 'bg-danger';
                            $statusIcon = 'stop-circle';
                            $statusText = 'Tidak Aktif';
                        }
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3 meja-item" data-status="{{ $meja->status }}">
                        <div class="card meja-card meja-card-modern h-100" 
                             data-meja-id="{{ $meja->id_meja }}" 
                             data-meja-nama="{{ $meja->nama_meja }}" 
                             data-meja-status="{{ $meja->status }}">
                            <div class="card-header p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ $statusClass }} text-white">{{ $statusText }}</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="text-center mb-3">
                                    <h5 class="card-title text-truncate">{{ $meja->nama_meja }}</h5>
                                </div>
                                <div class="meja-details">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-info-circle mr-2 text-info"></i>
                                        <small class="text-mobile-sm">Status: <strong>{{ ucfirst($meja->status) }}</strong></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt mr-2 text-success"></i>
                                        <small class="text-mobile-sm">Last Update: <strong>{{ now()->format('d/m/Y') }}</strong></small>
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
        @include('dashboardasyy.partials.statistik-chart')
        @include('dashboardasyy.partials.quick-stats')
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
<!-- Pass data to JavaScript -->
<script>
// Pass data to JavaScript
window.countAktif = {{ $countAktif }};
window.countMaintenance = {{ $countMaintenance }};
window.countTidakAktif = {{ $countTidakAktif }};
window.checksheetStoreUrl = '{{ route("dashboard.assy.checksheet.store") }}';
</script>
<!-- Dashboard Assy JS -->
<script src="{{ asset('js/dashboard-assy.js') }}"></script>
<script>
    window.notifUrl = "{{ route('notifications') }}";
</script>
<script src="{{ asset('js/notifikasi.js') }}"></script>
<script>
    $(document).ready(function() {
        window.startNotifPolling();
        // Event click untuk setiap card meja
        $('.meja-card').on('click', function() {
            var nama = $(this).data('meja-nama');
            var status = $(this).data('meja-status');
            var id = $(this).data('meja-id');
            console.log('card clicked', nama, status, id); // Tambahkan ini

            // Set data ke modal (jika ingin menampilkan nama/status di modal)
            $('#detail-nama-mesin').text(nama);
            $('#detail-status-mesin').text(status.charAt(0).toUpperCase() + status.slice(1));
            $('#form_mesin_id').val(id);

            // Kosongkan/isi field lain sesuai kebutuhan

            // Tampilkan modal
            $('#machineDetailModal').modal('show');
        });
    });
</script>
@endpush 