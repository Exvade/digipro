@extends('layouts.app')

@section('title', 'Report Checksheet' . (auth()->user()->role === 'leader' && isset($userDepartment) ? ' - ' . ucfirst($userDepartment) : ''))

@section('content')
<div class="report-container">
    <!-- Department Restriction Notice for Leaders -->
    @if(auth()->user()->role === 'leader' && isset($userDepartment))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Bagian Terbatas:</strong> Anda hanya dapat melihat dan membuat laporan untuk Bagian <strong>{{ ucfirst($userDepartment) }}</strong>.
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Section for Super Admin, Kabag, Manager Plant, and Leader -->
    @if(in_array(auth()->user()->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filter Laporan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="filter_grup_id" class="form-label">Grup</label>
                                <select class="form-control" id="filter_grup_id" name="filter_grup_id" @if(auth()->user()->role === 'foreman') disabled @endif>
                                    <option value="">Semua Grup</option>
                                    @foreach($grups as $grup)
                                    @if(auth()->user()->role !== 'foreman' || auth()->user()->grup_id == $grup->id)
                                    <option value="{{ $grup->id }}" {{ request('filter_grup_id', auth()->user()->role === 'foreman' ? auth()->user()->grup_id : null) == $grup->id ? 'selected' : '' }}>
                                        {{ $grup->nama_grup }}
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                                @if(auth()->user()->role === 'foreman')
                                <small class="form-text text-muted">Anda hanya dapat melihat laporan untuk grup Anda sendiri.</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="report_type_selector" class="form-label">Jenis Laporan</label>
                                <select class="form-control" id="report_type_selector" name="report_type">
                                    <option value="general" {{ $reportType === 'general' ? 'selected' : '' }}>Semua Laporan</option>
                                    <option value="assy" {{ $reportType === 'assy' ? 'selected' : '' }}>Laporan Assy</option>
                                    <option value="inject" {{ $reportType === 'inject' ? 'selected' : '' }}>Laporan Inject</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary flex-fill" onclick="applyFilters()">
                                    <i class="fas fa-search mr-1"></i>Terapkan
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times mr-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tab Filter Section --}}
    @if(in_array(auth()->user()->role, ['super_admin', 'kabag', 'manager_plant', 'foreman']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsReport" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $reportType === 'general' ? 'active' : '' }}" id="all-tab-report" href="#" data-bagian="general">
                                <i class="fas fa-list"></i> All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $reportType === 'inject' ? 'active' : '' }}" id="inject-tab-report" href="#" data-bagian="inject">
                                <i class="fas fa-cog"></i> Inject
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $reportType === 'assy' ? 'active' : '' }}" id="assy-tab-report" href="#" data-bagian="assy">
                                <i class="fas fa-tools"></i> Assy
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-clipboard-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Checksheet</span>
                    <span class="info-box-number">{{ $totalChecksheets }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $pendingChecksheets }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Approved</span>
                    <span class="info-box-number">{{ $approvedChecksheets }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rejected</span>
                    <span class="info-box-number">{{ $rejectedChecksheets }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Specific Statistics -->
    @if($reportType === 'assy' && $departmentSpecificData)
    @include('report.partials.assy-stats')
    @endif

    @if($reportType === 'inject' && $departmentSpecificData)
    @include('report.partials.inject-stats')
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Recent Checksheets based on department -->
            @if($reportType === 'assy')
            @include('report.partials.assy-checksheets')
            @elseif($reportType === 'inject')
            @include('report.partials.inject-checksheets')
            @else
            @include('report.partials.general-checksheets')
            @endif
        </div>

        <div class="col-md-4">
            <!-- Statistics based on department -->
            @if($reportType === 'assy')
            @include('report.partials.assy-sidebar-stats')
            @elseif($reportType === 'inject')
            @include('report.partials.inject-sidebar-stats')
            @else
            @include('report.partials.general-sidebar-stats')
            @endif
        </div>
    </div>

    <!-- Department Specific Modal Forms -->
    @if($reportType === 'assy')
    @include('report.partials.assy-modal-form')
    @elseif($reportType === 'inject')
    @include('report.partials.inject-modal-form')
    @else
    @include('report.partials.general-modal-form')
    @endif
</div>

<!-- AMTPM Example Modal -->
<div class="modal fade" id="amptmExampleModal" tabindex="-1" role="dialog" aria-labelledby="amptmExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amptmExampleModalLabel">
                    <i class="fas fa-clipboard-list mr-2"></i>Contoh Standar AMTPM
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Petunjuk:</strong> Berikut adalah contoh dokumen Standar AMTPM yang dapat digunakan sebagai referensi.
                </div>
                <div class="pdf-container" style="height: 500px;">
                    <iframe src="{{ asset('images/barang/1AMTPM.pdf') }}"
                        width="100%"
                        height="100%"
                        style="border: 1px solid #ddd; border-radius: 5px;">
                        <p>Browser Anda tidak mendukung tampilan PDF.
                            <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank">Klik di sini untuk mengunduh</a>
                        </p>
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i>Unduh PDF
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
<link rel="stylesheet" href="{{ asset('css/report.css') }}">
<link rel="stylesheet" href="{{ asset('css/report-index.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<div id="report-config"
    data-report-type="{{ $reportType ?? '' }}"
    data-report-store-url="{{ route('report.store') }}"
    data-export-excel-url="{{ route('report.export.excel') }}"
    data-export-pdf-url="{{ route('report.export.pdf') }}"
    data-grups="{{ json_encode($grups ?? []) }}"
    data-users="{{ json_encode($users ?? []) }}"
    data-user-department="{{ $userDepartment ?? '' }}"
    data-user-role="{{ auth()->user()->role ?? '' }}"
    style="display: none;">
</div>
<script src="{{ asset('js/report.js') }}"></script>
<script src="{{ asset('js/report-page.js') }}"></script>
<script src="{{ asset('js/report-index-init.js') }}"></script>
@endpush