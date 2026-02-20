@extends('layouts.app')

@section('title', 'Approval Checksheet - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Approval CSS -->
<link rel="stylesheet" href="{{ asset('css/approval.css') }}">
<!-- Planning CSS for Tab Filter -->
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Approval Checksheet
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Alert Messages --}}
@if ((auth()->user()->role === 'leader' || auth()->user()->role === 'foreman') && session('kabag_approved') && !empty(session('kabag_approved')))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="icon fas fa-check mr-2"></i>{{ session('kabag_approved') }}
</div>
@endif

{{-- Filter Buttons --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-info filter-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filter & Aksi
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Filter Grup</label>
                            <select class="form-control" id="grup_filter" name="grup_filter">
                                <option value="">Semua Grup</option>
                                @foreach ($grups as $grup)
                                <option value="{{ $grup->id }}"
                                    {{ $selectedGrup == $grup->id ? 'selected' : '' }}>
                                    {{ $grup->nama_grup }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="applyFilter()">
                                    <i class="fas fa-filter mr-1"></i>Filter
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFilter()">
                                    <i class="fas fa-redo mr-1"></i>Reset
                                </button>
                                <a href="{{ route('approval.history') }}" class="btn btn-info">
                                    <i class="fas fa-history mr-1"></i>Riwayat
                                </a>
                                @if (auth()->user()->role === 'kabag')
                                <button type="button" class="btn btn-success" id="approveAllBtn">
                                    <i class="fas fa-check-double mr-1"></i>Approval Semua
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tab Filter Section --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsApprovalChecksheet" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab-approval-cs" href="#" data-bagian="all">
                            <i class="fas fa-list"></i> All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inject-tab-approval-cs" href="#" data-bagian="inject">
                            <i class="fas fa-cog"></i> Inject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assy-tab-approval-cs" href="#" data-bagian="assy">
                            <i class="fas fa-tools"></i> Assy
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Table Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Checksheet Pending Approval
                </h3>
            </div>
            <div class="card-body">
                @if ($checksheets->count() > 0)
                <div class="table-responsive">
                    <table id="approvalTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:5%;">No</th>
                                <th style="width:15%;">Tanggal</th>
                                @if ($filter === 'all' || $filter === 'inject')
                                <th style="width:12%;">Mesin</th>
                                @endif
                                @if ($filter === 'all' || $filter === 'assy')
                                <th style="width:12%;">Meja</th>
                                @endif
                                <th style="width:20%;">Part</th>
                                <th style="width:15%;">User</th>
                                <th style="width:10%;">Grup</th>
                                <th class="text-center" style="width:8%;">Shift</th>
                                <th class="text-center" style="width:10%;">Status</th>
                                <th class="text-center" style="width:15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($checksheets as $index => $checksheet)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $checksheet->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $checksheet->created_at->format('H:i') }}</small>
                                </td>
                                @if ($filter === 'all' || $filter === 'inject')
                                <td>{{ $checksheet->mesin->nama_mesin ?? '-' }}</td>
                                @endif
                                @if ($filter === 'all' || $filter === 'assy')
                                <td>{{ $checksheet->meja->nama_meja ?? '-' }}</td>
                                @endif
                                <td>
                                    <div class="font-weight-bold">{{ $checksheet->part->nama_part ?? '-' }}</div>
                                </td>
                                <td>{{ $checksheet->user->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $checksheet->user->grup->nama_grup ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center action-buttons-container">
                                        <button type="button" class="btn btn-info btn-sm btn-view-checksheet" title="Lihat Detail"
                                            data-id="{{ $checksheet->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm btn-approve-checksheet" title="Setujui"
                                            data-id="{{ $checksheet->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btn-reject-checksheet" title="Tolak"
                                            data-id="{{ $checksheet->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard-check fa-3x mb-3 d-block"></i>
                    <h5>Tidak ada checksheet yang menunggu approval</h5>
                    <p class="mb-0">
                        @if ($filter === 'inject')
                        Tidak ada checksheet inject yang menunggu approval
                        @elseif($filter === 'assy')
                        Tidak ada checksheet assy yang menunggu approval
                        @else
                        Semua checksheet telah diproses
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Checksheet Modal -->
<div class="modal fade" id="viewChecksheetModal" tabindex="-1" role="dialog"
    aria-labelledby="viewChecksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewChecksheetModalLabel">
                    <i class="fas fa-eye mr-2"></i>Detail Checksheet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="checksheetDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check mr-2"></i>Setujui Checksheet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approveForm">
                <div class="modal-body">
                    <input type="hidden" id="approve_checksheet_id" name="checksheet_id">
                    <div class="form-group">
                        <label for="approval_note">Catatan (Opsional)</label>
                        <textarea class="form-control" id="approval_note" name="approval_note" rows="3"
                            placeholder="Masukkan catatan approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times mr-2"></i>Tolak Checksheet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <input type="hidden" id="reject_checksheet_id" name="checksheet_id">
                    <div class="form-group">
                        <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                            placeholder="Masukkan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AMTPM Example Modal -->
<div class="modal fade" id="amptmExampleModal" tabindex="-1" role="dialog"
    aria-labelledby="amptmExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                    <strong>Petunjuk:</strong> Berikut adalah contoh dokumen Standar AMTPM yang dapat digunakan sebagai
                    referensi.
                </div>
                <div class="pdf-container" style="height: 500px;">
                    <iframe src="{{ asset('images/barang/1AMTPM.pdf') }}" width="100%" height="100%"
                        style="border: 1px solid #ddd; border-radius: 5px;">
                        <p>Browser Anda tidak mendukung tampilan PDF.
                            <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank">Klik di sini untuk
                                mengunduh</a>
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

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- Custom Approval JS -->
<script src="{{ asset('js/approval.js') }}"></script>

{{-- Set Active Tab Based on Filter --}}
<script>
    $(document).ready(function() {
        // Set active tab based on current filter
        const currentFilter = '{{ $filter }}';
        $('#bagianTabsApprovalChecksheet .nav-link').removeClass('active');

        if (currentFilter === 'inject') {
            $('#inject-tab-approval-cs').addClass('active');
        } else if (currentFilter === 'assy') {
            $('#assy-tab-approval-cs').addClass('active');
        } else {
            $('#all-tab-approval-cs').addClass('active');
        }
    });
</script>
@endpush