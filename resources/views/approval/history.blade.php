@extends('layouts.app')

@section('title', 'Riwayat Approval Checksheet')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Riwayat Approval Checksheet
                </h3>
                <div class="card-tools">
                    <a href="{{ route('approval.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Approval
                    </a>
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
                        <table class="table table-bordered table-striped" id="historyTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Mesin/Meja</th>
                                    <th>Part</th>
                                    <th>User</th>
                                    <th>Grup</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Approver</th>
                                    <th>Tanggal Approval</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checksheets as $index => $checksheet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $checksheet->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($checksheet->mesin)
                                            <span class="badge badge-info">{{ $checksheet->mesin->nama_mesin }}</span>
                                        @elseif($checksheet->meja)
                                            <span class="badge badge-warning">{{ $checksheet->meja->nama_meja }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $checksheet->part->nama_part ?? '-' }}</td>
                                    <td>{{ $checksheet->user->nama ?? '-' }}</td>
                                    <td>{{ $checksheet->user->grup->nama_grup ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                                    </td>
                                    <td>
                                        @if($checksheet->approval_status === 'approved')
                                            <span class="badge badge-success">Disetujui</span>
                                        @else
                                            <span class="badge badge-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>{{ $checksheet->approver->nama ?? '-' }}</td>
                                    <td>{{ $checksheet->approved_at ? $checksheet->approved_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewChecksheet({{ $checksheet->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada riwayat approval</h5>
                        <p class="text-muted">Belum ada checksheet yang telah diproses</p>
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
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#historyTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });
});

function viewChecksheet(id) {
    $.get(`/approval/${id}`, function(response) {
        $('#checksheetDetailContent').html(response);
        $('#viewChecksheetModal').modal('show');
    });
}

function applyFilter() {
    const grupFilter = $('#grup_filter').val();
    const currentUrl = new URL(window.location);
    
    // Update or add grup filter parameter
    if (grupFilter) {
        currentUrl.searchParams.set('grup_filter', grupFilter);
    } else {
        currentUrl.searchParams.delete('grup_filter');
    }
    
    window.location.href = currentUrl.toString();
}

function clearFilter() {
    const currentUrl = new URL(window.location);
    
    // Remove grup filter parameter
    currentUrl.searchParams.delete('grup_filter');
    
    window.location.href = currentUrl.toString();
}
</script>
@endpush 