@extends('planning.app')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Approval Planning Produksi
                    </h1>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- Filter Tanggal --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        Filter Planning
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('planning.approval.page') }}" class="d-flex align-items-center">
                        <div class="form-group mb-0 mr-3">
                            <label for="tanggal" class="mr-2">Tanggal Planning:</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal" name="tanggal" 
                                value="{{ $filterTanggal }}" style="width: auto; display: inline-block;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('planning.approval.page') }}" class="btn btn-secondary btn-sm ml-2">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi:</strong> Halaman ini menampilkan planning produksi yang menunggu approval dari Kabag PPIC.
                Setelah di-approve, QR code akan otomatis dibuat dengan informasi approver.
            </div>
        </div>
    </div>

    {{-- Tab Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsApproval" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab-approval" href="#" data-bagian="all">
                                <i class="fas fa-list"></i> All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inject-tab-approval" href="#" data-bagian="inject">
                                <i class="fas fa-cog"></i> Inject
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="assy-tab-approval" href="#" data-bagian="assy">
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
                        Daftar Planning Pending Approval
                    </h3>
                </div>
                <div class="card-body">
                    @if($plannings->count() > 0)
                        <div class="table-responsive">
                            <table id="approvalTable" class="table table-striped table-bordered table-hover">
                                <thead class="text-center bg-primary text-white">
                                    <tr>
                                        <th style="width:50px;">No</th>
                                        <th>No Part</th>
                                        <th>Nama Part</th>
                                        <th>Bagian</th>
                                        <th>Mesin/Meja</th>
                                        <th>Tanggal</th>
                                        <th style="width:150px;">Qty Planning</th>
                                        <th style="width:200px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plannings as $planning)
                                        <tr>
                                            <td class="text-center">
                                                {{ $plannings->firstItem() + $loop->index }}
                                            </td>
                                            <td class="text-center">
                                                {{ $planning->part->no_parts ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $planning->part->nama_part ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($planning->bagian == 'assy')
                                                    <span class="badge badge-info">Assembly</span>
                                                @else
                                                    <span class="badge badge-primary">Inject</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($planning->bagian == 'assy')
                                                    {{ $planning->meja->nama_meja ?? '-' }}
                                                @else
                                                    {{ $planning->mesin->nama_mesin ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($planning->tanggal)->format('d-m-Y') }}
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ number_format($planning->qty_planning_produksi) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-success btn-sm approveBtn mr-1" 
                                                    data-id="{{ $planning->id }}"
                                                    data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                                    data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                                    data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                                    title="Approve Planning">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn btn-danger btn-sm rejectBtn mr-1" 
                                                    data-id="{{ $planning->id }}"
                                                    title="Reject Planning">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                                <button class="btn btn-info btn-sm viewBtn" 
                                                    data-id="{{ $planning->id }}"
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($plannings->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $plannings->appends(['tanggal' => $filterTanggal])->links() }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-info-circle"></i>
                            Tidak ada planning yang menunggu approval untuk tanggal yang dipilih.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Approval --}}
    <div class="modal fade" id="modalApproval" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white" id="approvalModalHeader">
                    <h5 class="modal-title" id="modalApprovalLabel">
                        <i class="fas fa-check-circle"></i> Approve Planning
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formApproval" method="POST" data-action-url="{{ route('planning.approval') }}">
                        @csrf
                        <input type="hidden" id="approval_planning_id" name="planning_id">
                        <input type="hidden" id="approval_action" name="action" value="approve">
                        
                        <div id="approveContent">
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle"></i>
                                <strong>Konfirmasi Approval</strong><br>
                                Apakah Anda yakin ingin menyetujui planning produksi ini? Setelah di-approve, QR code akan otomatis dibuat dengan informasi approver <strong>Ariyanto</strong>.
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-info-circle"></i> Detail Planning:</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th width="150">No Part:</th>
                                            <td id="approval-no_part">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Part:</th>
                                            <td id="approval-nama_part">-</td>
                                        </tr>
                                        <tr>
                                            <th>Qty Planning:</th>
                                            <td id="approval-qty">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-success" id="submitApprovalBtn">
                        <i class="fas fa-check"></i> Konfirmasi Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Reject --}}
    <div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle"></i> Reject Planning
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formReject" method="POST" data-action-url="{{ route('planning.approval') }}">
                        @csrf
                        <input type="hidden" id="reject_planning_id" name="planning_id">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Konfirmasi Rejection</strong><br>
                            Apakah Anda yakin ingin menolak planning produksi ini?
                        </div>
                        
                        <div class="form-group">
                            <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" 
                                placeholder="Masukkan alasan penolakan..." required></textarea>
                            <small class="form-text text-muted">Alasan penolakan wajib diisi</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmReject">
                        <i class="fas fa-times"></i> Konfirmasi Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modal Show --}}
    @include('planning.show')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/planning.css') }}">
    <style>
        #qrCodeImage img {
            max-width: 300px;
            border: 2px solid #28a745;
            padding: 10px;
            background: white;
        }
    </style>
@endpush

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/planning/approval.js') }}"></script>
@endpush

