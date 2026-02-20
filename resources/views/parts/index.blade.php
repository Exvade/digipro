@extends('user.app')

@section('title', 'Master Data Parts - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<!-- Custom Parts CSS -->
<link rel="stylesheet" href="{{ asset('css/parts-index.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Data Parts
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Alert Messages --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="icon fas fa-check mr-2"></i>{{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="icon fas fa-ban mr-2"></i>
    <ul class="mb-0 pl-3">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Action Button --}}
<div class="row mb-3">
    <div class="col-12 text-right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPartModal">
            <i class="fas fa-plus mr-1"></i> Tambah Part
        </button>
    </div>
</div>

{{-- Table Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Parts
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="partsTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:5%;">No</th>
                                <th style="width:15%;">No Part</th>
                                <th style="width:15%;">No Barang</th>
                                <th style="width:25%;">Nama Part</th>
                                <th style="width:15%;">Type</th>
                                <th class="text-center" style="width:10%;">Quantity</th>
                                <th class="text-center" style="width:10%;">Bruto (kg)</th>
                                <th class="text-center" style="width:20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="partsTableBody">
                            @forelse($parts as $index => $part)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ $part->no_parts }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $part->no_barang ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate-2" title="{{ $part->nama_part }}">
                                        {{ $part->nama_part }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $part->type }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info font-weight-bold">{{ number_format($part->quantity) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success font-weight-bold">{{ number_format($part->bruto, 2) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-info btn-sm btn-view-part" data-part-id="{{ $part->id }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm btn-edit-part" data-part-id="{{ $part->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-part" data-part-id="{{ $part->id }}" data-part-name="{{ $part->nama_part }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <h5>Tidak ada data parts</h5>
                                    <p class="mb-0">Belum ada data parts yang tersedia. Silakan tambahkan part baru.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Create Modal -->
@include('parts.create')

<!-- Include Edit Modal -->
@include('parts.update')

<!-- View Part Modal -->
<div class="modal fade" id="viewPartModal" tabindex="-1" aria-labelledby="viewPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPartModalLabel">
                    <i class="fas fa-eye mr-2"></i>Detail Part
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">No Part</label>
                            <p id="view_no_parts" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">No Barang</label>
                            <p id="view_no_barang" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Type</label>
                            <p id="view_type" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Nama Part</label>
                    <p id="view_nama_part" class="form-control-plaintext"></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Quantity</label>
                            <p id="view_quantity" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Bruto (kg)</label>
                            <p id="view_bruto" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<!-- Custom Parts JS -->
<script src="{{ asset('js/parts-index.js') }}"></script>
@endpush