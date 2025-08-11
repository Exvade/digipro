@extends('layouts.app')

@section('title', 'Master Data Parts')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parts-index.css') }}">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    table#partsTable thead th {
        color: #212529 !important; /* warna hitam/abu gelap */
        background-color: #e9ecef !important; /* abu muda */
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    <span class="d-none d-sm-inline">Master Data Parts</span>
                    <span class="d-sm-none">Parts</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createPartModal">
                        <i class="fas fa-plus mr-1"></i>
                        <span class="d-none d-sm-inline">Tambah Part</span>
                        <span class="d-sm-none">Tambah</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <h4 class="font-weight-bold">Daftar Parts</h4>
                </div>
                <!-- Parts Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="partsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">No Part</th>
                                <th width="25%">Nama Part</th>
                                <th width="15%">Type</th>
                                <th width="10%">Quantity</th>
                                <th width="10%">Bruto</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="partsTableBody">
                            @forelse($parts as $index => $part)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ $part->no_parts }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate-2" title="{{ $part->nama_part }}">
                                        {{ $part->nama_part }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $part->type }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ number_format($part->quantity) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ number_format($part->bruto, 2) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2" role="group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewPart({{ $part->id }})" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editPart({{ $part->id }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deletePart({{ $part->id }}, '{{ $part->nama_part }}')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="empty-state text-center">
                                    <i class="fas fa-inbox"></i>
                                    <h4>Tidak ada data parts</h4>
                                    <p>Belum ada data parts yang tersedia. Silakan tambahkan part baru.</p>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPartModalLabel">
                    <i class="fas fa-eye mr-2"></i>Detail Part
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">No Part</label>
                            <p id="view_no_parts" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
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
<script src="{{ asset('js/parts-index.js') }}"></script>
<script>
$(document).ready(function() {
    $('#partsTable').DataTable();
});
</script>
@endpush
