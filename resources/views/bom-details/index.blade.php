@extends('bom-details.app')

@section('title', 'Bill of Material - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<!-- Custom BOM Details CSS -->
<link rel="stylesheet" href="{{ asset('css/bom-details-index.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Bill of Material
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Action Button --}}
<div class="row mb-3">
    <div class="col-12 text-right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
            <i class="fas fa-plus"></i> Tambah BOM
        </button>
    </div>
</div>

{{-- Table Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Daftar Bill of Material
                </h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon fas fa-check mr-2"></i>{{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="icon fas fa-ban mr-2"></i>{{ session('error') }}
                </div>
                @endif

                <div class="table-responsive">
                    <table id="tableBomDetails" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th style="width:120px;">No Mold</th>
                                <th>Nama Part</th>
                                <th class="text-center" style="width:100px;">Cavity Mold</th>
                                <th class="text-center" style="width:120px;">Cycle Time (s)</th>
                                <th style="width:120px;">Resin Type</th>
                                <th class="text-center" style="width:100px;">Shot Weight</th>
                                <th>Remarks</th>
                                <th class="text-center" style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bomDetails as $index => $bom)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $bom->moldshop->no_mold ?? '-' }}</div>
                                </td>
                                <td>{{ $bom->part->nama_part ?? '-' }}</td>
                                <td class="text-center">{{ $bom->cavity_mold ?? '-' }}</td>
                                <td class="text-center">{{ $bom->cycle_time_std ?? '-' }}</td>
                                <td>{{ $bom->resin_type ?? '-' }}</td>
                                <td class="text-center">{{ $bom->shot_weight ?? '-' }}</td>
                                <td>{{ $bom->remarks ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button"
                                            class="btn btn-sm btn-warning editBtn"
                                            title="Edit"
                                            data-id="{{ $bom->id }}"
                                            data-moldshop_id="{{ $bom->moldshop_id }}"
                                            data-part_id="{{ $bom->part_id }}"
                                            data-cavity_mold="{{ $bom->cavity_mold }}"
                                            data-cycle_time_std="{{ $bom->cycle_time_std }}"
                                            data-resin_type="{{ $bom->resin_type ?? '' }}"
                                            data-shot_weight="{{ $bom->shot_weight ?? '' }}"
                                            data-remarks="{{ $bom->remarks ?? '' }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('bom-details.destroy', $bom->id) }}"
                                            method="POST"
                                            style="display:inline;"
                                            class="deleteForm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-danger deleteBtn"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox mr-2"></i>Belum ada data BOM
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

{{-- Include Modals --}}
@include('bom-details.create')
@include('bom-details.update')
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom BOM Details JS -->
<script src="{{ asset('js/bom-details-index.js') }}"></script>
@endpush