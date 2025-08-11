@extends('layouts.app')

@section('title', 'Approval Checksheet')

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
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Approval Checksheet
                </h3>
                <div class="card-tools">
                    <div class="btn-group" role="group">
                        <a href="{{ route('approval.index', ['filter' => 'all']) }}" 
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-list mr-1"></i>All
                        </a>
                        <a href="{{ route('approval.index', ['filter' => 'inject']) }}" 
                           class="btn btn-sm {{ $filter === 'inject' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-cog mr-1"></i>Inject
                        </a>
                        <a href="{{ route('approval.index', ['filter' => 'assy']) }}" 
                           class="btn btn-sm {{ $filter === 'assy' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-tools mr-1"></i>Assy
                        </a>
                    </div>
                    <a href="{{ route('approval.history') }}" class="btn btn-info btn-sm ml-2">
                        <i class="fas fa-history mr-1"></i>Riwayat
                    </a>
                    @if(auth()->user()->role === 'kabag')
                        <button type="button" class="btn btn-success btn-sm ml-2" id="approveAllBtn">
                            <i class="fas fa-check-double mr-1"></i>Approval Semua
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="grup_filter">Filter Grup</label>
                            <select class="form-control" id="grup_filter" name="grup_filter">
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
                                <button type="button" class="btn btn-primary btn-sm" onclick="applyFilter()">
                                    <i class="fas fa-filter mr-1"></i>Terapkan Filter
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilter()">
                                    <i class="fas fa-times mr-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @if($checksheets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="approvalTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    @if($filter === 'all' || $filter === 'inject')
                                        <th>Mesin</th>
                                    @endif
                                    @if($filter === 'all' || $filter === 'assy')
                                        <th>Meja</th>
                                    @endif
                                    <th>Part</th>
                                    <th>User</th>
                                    <th>Grup</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checksheets as $index => $checksheet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $checksheet->created_at->format('d/m/Y H:i') }}</td>
                                    @if($filter === 'all' || $filter === 'inject')
                                        <td>{{ $checksheet->mesin->nama_mesin ?? '-' }}</td>
                                    @endif
                                    @if($filter === 'all' || $filter === 'assy')
                                        <td>{{ $checksheet->meja->nama_meja ?? '-' }}</td>
                                    @endif
                                    <td>{{ $checksheet->part->nama_part ?? '-' }}</td>
                                    <td>{{ $checksheet->user->nama ?? '-' }}</td>
                                    <td>{{ $checksheet->user->grup->nama_grup ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">Pending</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewChecksheet({{ $checksheet->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" onclick="approveChecksheet({{ $checksheet->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectChecksheet({{ $checksheet->id }})">
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
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada checksheet yang menunggu approval</h5>
                        <p class="text-muted">
                            @if($filter === 'inject')
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
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                        <textarea class="form-control" id="approval_note" name="approval_note" rows="3" placeholder="Masukkan catatan approval..."></textarea>
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
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
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
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('js/approval.js') }}"></script>
@endpush 