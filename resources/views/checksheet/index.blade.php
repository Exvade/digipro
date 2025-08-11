@extends('layouts.app')

@section('title', 'Data Checksheet')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Data Checksheet
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createChecksheetModal">
                        <i class="fas fa-plus mr-1"></i>Tambah Checksheet
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-filter mr-2"></i>Filter Data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('checksheet.index') }}" id="filterForm">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="grup_id">Filter Grup</label>
                                                <select class="form-control" id="grup_id" name="grup_id">
                                                    <option value="">Semua Grup</option>
                                                    @foreach($grups as $grup)
                                                    <option value="{{ $grup->id }}" {{ $selectedGrup == $grup->id ? 'selected' : '' }}>
                                                        {{ $grup->nama_grup }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-search mr-1"></i>Filter
                                                    </button>
                                                    <a href="{{ route('checksheet.index') }}" class="btn btn-secondary">
                                                        <i class="fas fa-times mr-1"></i>Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Inject Section</span>
                                <span class="info-box-number">{{ $injectChecksheets->count() }} Checksheets</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    @php
                                        $injectPending = $injectChecksheets->where('approval_status', 'pending')->count();
                                        $injectApproved = $injectChecksheets->where('approval_status', 'approved')->count();
                                        $injectRejected = $injectChecksheets->where('approval_status', 'rejected')->count();
                                    @endphp
                                    Pending: {{ $injectPending }} | Approved: {{ $injectApproved }} | Rejected: {{ $injectRejected }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Assy Section</span>
                                <span class="info-box-number">{{ $assyChecksheets->count() }} Checksheets</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    @php
                                        $assyPending = $assyChecksheets->where('approval_status', 'pending')->count();
                                        $assyApproved = $assyChecksheets->where('approval_status', 'approved')->count();
                                        $assyRejected = $assyChecksheets->where('approval_status', 'rejected')->count();
                                    @endphp
                                    Pending: {{ $assyPending }} | Approved: {{ $assyApproved }} | Rejected: {{ $assyRejected }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="checksheetTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="inject-tab" data-toggle="tab" href="#inject" role="tab" aria-controls="inject" aria-selected="true">
                            <i class="fas fa-cogs mr-1"></i>Inject
                            <span class="badge badge-primary ml-1">{{ $injectChecksheets->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="assy-tab" data-toggle="tab" href="#assy" role="tab" aria-controls="assy" aria-selected="false">
                            <i class="fas fa-tools mr-1"></i>Assy
                            <span class="badge badge-info ml-1">{{ $assyChecksheets->count() }}</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content" id="checksheetTabContent">
                    <!-- Inject Tab -->
                    <div class="tab-pane fade show active" id="inject" role="tabpanel" aria-labelledby="inject-tab">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="injectChecksheetTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Mesin</th>
                                        <th>Part</th>
                                        <th>User</th>
                                        <th>Grup</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($injectChecksheets as $index => $checksheet)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $checksheet->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $checksheet->mesin->nama_mesin ?? '-' }}</td>
                                        <td>{{ $checksheet->part->nama_part ?? '-' }}</td>
                                        <td>{{ $checksheet->user->nama ?? '-' }}</td>
                                        <td>{{ $checksheet->user->grup->nama_grup ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                                        </td>
                                        <td>
                                            @if($checksheet->approval_status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($checksheet->approval_status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-sm" onclick="viewChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="editChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Assy Tab -->
                    <div class="tab-pane fade" id="assy" role="tabpanel" aria-labelledby="assy-tab">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="assyChecksheetTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Meja</th>
                                        <th>Part</th>
                                        <th>User</th>
                                        <th>Grup</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assyChecksheets as $index => $checksheet)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $checksheet->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $checksheet->meja->nama_meja ?? '-' }}</td>
                                        <td>{{ $checksheet->part->nama_part ?? '-' }}</td>
                                        <td>{{ $checksheet->user->nama ?? '-' }}</td>
                                        <td>{{ $checksheet->user->grup->nama_grup ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                                        </td>
                                        <td>
                                            @if($checksheet->approval_status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($checksheet->approval_status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-sm" onclick="viewChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="editChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteChecksheet({{ $checksheet->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Checksheet Modal -->
<div class="modal fade" id="createChecksheetModal" tabindex="-1" role="dialog" aria-labelledby="createChecksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createChecksheetModalLabel">
                    <i class="fas fa-plus mr-2"></i>Tambah Checksheet Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="createChecksheetContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Checksheet Modal -->
<div class="modal fade" id="editChecksheetModal" tabindex="-1" role="dialog" aria-labelledby="editChecksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editChecksheetModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Checksheet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editChecksheetContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- View Checksheet Modal -->
<div class="modal fade" id="viewChecksheetModal" tabindex="-1" role="dialog" aria-labelledby="viewChecksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewChecksheetModalLabel">
                    <i class="fas fa-eye mr-2"></i>Detail Checksheet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewChecksheetContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
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
                        <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank">Klik di sini untuk mengunduh</a></p>
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
<link rel="stylesheet" href="{{ asset('css/checksheet-index.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('js/checksheet-index.js') }}"></script>
@endpush 