@extends('layouts.app')

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Planning Produksi
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')

{{-- Alert Section for Import Errors/Success --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('import_errors'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan pada beberapa baris data:</h5>
    <hr>
    <ul class="mb-0" style="max-height: 200px; overflow-y: auto;">
        @foreach(session('import_errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

{{-- Action Buttons --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-wrap">
                <button class="btn btn-success mr-2" data-toggle="modal" data-target="#modalCetak">
                    <i class="fas fa-print"></i> Cetak Planning
                </button>

                {{-- Filter Tanggal (Dropdown) --}}
                <form method="GET" action="{{ route('planning.index') }}" class="d-inline" id="formFilterIndex">
                    <div class="d-flex align-items-center">
                        <select class="form-control form-control-sm" id="filterTanggalIndex" style="width: auto; display: inline-block;">
                            <option value="today">Hari Ini ({{ now()->format('d-m-Y') }})</option>
                            <option value="tomorrow">Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                            <option value="custom">Tanggal Spesifik</option>
                        </select>
                        <input type="date" id="customDateIndex" name="tanggal" class="form-control form-control-sm ml-2" style="width: auto; display: none;">
                    </div>
                </form>

                @isset($filterTanggal)
                <span class="badge badge-pill badge-info ml-2">
                    Menampilkan: {{ \Carbon\Carbon::parse($filterTanggal)->format('d-m-Y') }}
                </span>
                @endisset
            </div>
            <div>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Tambah Planning
                </button>
                <button class="btn btn-success ml-2" data-toggle="modal" data-target="#modalImport">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tab Filter Section --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabs" role="tablist">
                    <!-- Sembunyikan All dan Assy -->
                    <li class="nav-item">
                        <a class="nav-link active" id="inject-tab" href="#" data-bagian="inject">
                            <i class="fas fa-cog"></i> Inject
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
                    Data Planning Produksi
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="planningTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>No Part</th>
                                <th>Nama Part</th>
                                <th>Bagian</th>
                                <th>Mesin</th>
                                <th style="width:150px;">Qty Planning</th>
                                <th style="width:120px;">Status Approval</th>
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
                                    {{ number_format($planning->qty_planning_produksi) }}
                                </td>
                                <td class="text-center">
                                    @if($planning->status_approval == 'approved')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                    @if($planning->approved_at)
                                    <br><small class="text-muted">
                                        {{ \Carbon\Carbon::parse($planning->approved_at)->format('d-m-Y H:i') }}
                                    </small>
                                    @endif
                                    @elseif($planning->status_approval == 'rejected')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                    @if($planning->approved_at)
                                    <br><small class="text-muted">
                                        {{ \Carbon\Carbon::parse($planning->approved_at)->format('d-m-Y H:i') }}
                                    </small>
                                    @endif
                                    @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Tombol Detail --}}
                                    <button class="btn btn-info btn-sm showBtn mr-1" title="Lihat Detail"
                                        data-id="{{ $planning->id }}"
                                        data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                        data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                        data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                        data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                        data-tanggal="{{ $planning->tanggal }}"
                                        data-lot="{{ $planning->lot_produksi }}"
                                        data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                        data-no_barang="{{ $planning->part->no_barang ?? '-' }}"
                                        data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                        data-type="{{ $planning->part->type ?? '-' }}"
                                        data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                        data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'>
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-warning btn-sm editBtn mr-1"
                                        data-id="{{ $planning->id }}"
                                        data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                        data-moldshop_id="{{ $planning->moldshop_id }}"
                                        data-mesin_id="{{ $planning->mesin_id }}"
                                        data-meja_id="{{ $planning->meja_id }}"
                                        data-part_id="{{ $planning->part_id }}"
                                        data-tanggal="{{ $planning->tanggal }}"
                                        data-lot_produksi="{{ $planning->lot_produksi }}"
                                        data-qty="{{ $planning->qty_planning_produksi }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                        title="Edit Planning">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Tombol Approval (Hanya untuk Kabag PPIC) --}}
                                    @if(auth()->user()->role == 'kabag' && auth()->user()->departemen == 'PPIC' && $planning->status_approval == 'pending')
                                    <button class="btn btn-success btn-sm approvalBtn mr-1"
                                        data-id="{{ $planning->id }}"
                                        data-action="approve"
                                        title="Approve Planning">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm approvalBtn mr-1"
                                        data-id="{{ $planning->id }}"
                                        data-action="reject"
                                        title="Reject Planning">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('planning.destroy', $planning->id) }}" method="POST"
                                        class="deleteForm d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($plannings->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $plannings->appends(['tanggal' => $filterTanggal ?? null])->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- === MODALS === --}}
@include('planning.create')
@include('planning.update')
@include('planning.show')

{{-- Modal Approval --}}
<div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-labelledby="modalApprovalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" id="approvalModalHeader">
                <h5 class="modal-title" id="modalApprovalLabel">
                    <i class="fas fa-check-circle"></i> Approval Planning
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formApproval" method="POST">
                    @csrf
                    <input type="hidden" id="approval_planning_id" name="planning_id">
                    <input type="hidden" id="approval_action" name="action">

                    <div id="approveContent" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle"></i>
                            <strong>Konfirmasi Approval</strong><br>
                            Apakah Anda yakin ingin menyetujui planning produksi ini?
                        </div>
                    </div>

                    <div id="rejectContent" style="display: none;">
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
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn" id="submitApprovalBtn" onclick="submitApproval()">
                    <i class="fas fa-check"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Filter Cetak --}}
<div class="modal fade" id="modalCetak" tabindex="-1" role="dialog" aria-labelledby="modalCetakLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCetakLabel">
                    <i class="fas fa-print"></i> Cetak Planning Produksi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCetak" method="GET" action="{{ route('planning.cetak') }}" target="_blank" data-base-url="{{ route('planning.cetak') }}">
                    <div class="form-group">
                        <label for="tanggalFilter">Pilih Tanggal Planning:</label>
                        <select class="form-control" id="tanggalFilter" name="tanggal">
                            <option value="besok">📅 Planning Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                            <option value="hari_ini">📅 Planning Hari Ini ({{ now()->format('d-m-Y') }})</option>
                            <option value="custom">📅 Tanggal Spesifik</option>
                        </select>
                    </div>

                    <div class="form-group" id="customDateGroup" style="display: none;">
                        <label for="customDate">Tanggal Spesifik:</label>
                        <input type="date" class="form-control" id="customDate" name="tanggal_custom">
                    </div>

                    <div class="form-group">
                        <label for="bagianFilter">Pilih Bagian Planning:</label>
                        <select class="form-control" id="bagianFilter" name="bagian">
                            <option value="inject">⚙️ Inject Saja</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Planning Besok:</strong> Planning yang akan dijalankan besok (biasanya dibuat hari ini)</li>
                            <li><strong>Planning Hari Ini:</strong> Planning produksi hari ini</li>
                            <li><strong>Tanggal Spesifik:</strong> Pilih tanggal tertentu untuk melihat planning</li>
                            <li><strong>Bagian:</strong> Pilih bagian yang ingin dicetak (Inject, Assy, atau Keduanya)</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success" onclick="submitCetak()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalImportLabel">
                    <i class="fas fa-file-excel"></i> Import Planning dari Excel
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('planning.import-excel') }}" method="POST" enctype="multipart/form-data" id="formImport">
                    @csrf
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Gunakan template yang disediakan agar format data sesuai sistem.
                        <br>
                        <a href="{{ route('planning.download-template') }}" class="btn btn-sm btn-light mt-2 text-primary font-weight-bold">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>

                    <div class="form-group">
                        <label for="excel_file">Upload File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                            <label class="custom-file-label" for="excel_file">Pilih file...</label>
                        </div>
                        <small class="form-text text-muted">Format yang didukung: .xlsx, .xls (Max 2MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="document.getElementById('formImport').submit()">
                    <i class="fas fa-upload"></i> Upload & Import
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Custom file input label change
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('excel_file');
        const fileLabel = document.querySelector('.custom-file-label[for="excel_file"]');

        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    fileLabel.textContent = e.target.files[0].name;
                } else {
                    fileLabel.textContent = 'Pilih file...';
                }
            });
        }
    });
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Planning Data (JSON) -->
<script type="application/json" id="planning-data">
    {
        "successMessage": @json(session('success')),
        "indexUrl": "{{ route('planning.index') }}",
        "cetakUrl": "{{ route('planning.cetak') }}",
        "todayYmd": "{{ now()->format('Y-m-d') }}",
        "tomorrowYmd": "{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}",
        "currentFilter": @json($filterTanggal ?? null)
    }
</script>

<!-- Planning Scripts -->
<script src="{{ asset('js/planning.js') }}?v={{ filemtime(public_path('js/planning.js')) }}"></script>
@endpush