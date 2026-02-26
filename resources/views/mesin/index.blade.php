@extends('mesin.app')

@section('title', 'Data Mesin')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<!-- Custom Mesin CSS -->
<link rel="stylesheet" href="{{ asset('css/mesin.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Data Mesin
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@php
$user = Auth::user();
$isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
@endphp

{{-- Action Button --}}
<div class="row mb-3">
    <div class="col-12 text-right">
        @if(!in_array($user->role, ['leader', 'staff']) || $isRMStaff)
        <button type="button" class="btn btn-primary btn-create-mesin">
            <i class="fas fa-plus"></i> Tambah Mesin
        </button>
        @endif
    </div>
</div>

{{-- Table Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Mesin
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
                    <table id="mesinTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Mesin</th>
                                <th>Brand</th>
                                <th>Tonase</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                @if(!in_array($user->role, ['staff', 'operator']) || $isRMStaff)
                                <th style="width:120px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mesins as $index => $mesin)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $mesin->nama_mesin }}</div>
                                </td>
                                <td>{{ $mesin->brand_mesin ?? '-' }}</td>
                                <td class="text-center">{{ $mesin->tonase_mesin }} Ton</td>
                                <td class="text-center">
                                    @if($mesin->status == 'running')
                                    <span class="badge badge-success">Running</span>
                                    @elseif($mesin->status == 'maintenance')
                                    <span class="badge badge-danger">BREAKDOWN {{ $mesin->stop_reason ? '('.$mesin->stop_reason.')' : '(MESIN RUSAK / ERROR)' }}</span>
                                    @elseif($mesin->status == 'offline')
                                    <span class="badge badge-dark">OFF {{ $mesin->stop_reason ? '('.$mesin->stop_reason.')' : '(NO PLAN)' }}</span>
                                    @elseif($mesin->status == 'stop')
                                    <span class="badge badge-warning">STAND BY {{ $mesin->stop_reason ? '('.$mesin->stop_reason.')' : '' }}</span>
                                    @else
                                    <span class="badge badge-secondary">{{ ucfirst($mesin->status ?? '-') }}</span>
                                    @endif
                                </td>
                                <td>{{ $mesin->keterangan ?? '-' }}</td>
                                @if(!in_array($user->role, ['staff', 'operator']) || $isRMStaff)
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if(!in_array($user->role, ['leader', 'staff', 'operator']) || $isRMStaff)
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-edit-mesin"
                                            title="Edit"
                                            data-mesin-id="{{ $mesin->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif

                                        @if(in_array($user->role, ['leader']))
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-edit-mesin"
                                            title="Update Status"
                                            data-mesin-id="{{ $mesin->id }}">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        @endif

                                        @if(!in_array($user->role, ['leader', 'staff', 'operator']))
                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete-mesin"
                                            title="Hapus"
                                            data-mesin-id="{{ $mesin->id }}"
                                            data-mesin-name="{{ $mesin->nama_mesin }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">
                    <i class="fas fa-plus mr-2 text-light"></i>Tambah Mesin
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-light">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="addModalBody">
                <!-- Form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit mr-2 text-light"></i>Edit Mesin
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-light">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editModalBody">
                <!-- Form will be loaded here -->
            </div>
        </div>
    </div>
</div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom Mesin JS -->
<script src="{{ asset('js/mesin.js') }}"></script>
<script>
    // Make route URLs available to JavaScript
    window.createModalUrl = '{{ route("mesin.create") }}';
</script>
@endpush