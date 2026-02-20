@extends('moldshop.app')

@section('title', 'Data Moldshop - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Moldshop CSS -->
<link rel="stylesheet" href="{{ asset('css/moldshop.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Data Mold
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
            <i class="fas fa-plus"></i> Tambah Mold
        </button>
    </div>
</div>

{{-- Table Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Mold
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
                    <table id="moldTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>No Mold</th>
                                <th>Nama Mold</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $startIndex = isset($moldshops) && method_exists($moldshops, 'firstItem')
                            ? $moldshops->firstItem()
                            : 1;
                            @endphp

                            @foreach ($moldshops as $index => $mold)
                            <tr>
                                <td class="text-center">{{ $startIndex + $index }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $mold->no_mold }}</div>
                                </td>
                                <td>{{ $mold->nama_mold }}</td>
                                <td>{{ $mold->type ?? '-' }}</td>
                                <td>{{ $mold->customer ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($mold->status == 'OK')
                                    <span class="badge badge-success">OK</span>
                                    @elseif ($mold->status == 'REPAIR')
                                    <span class="badge badge-danger">REPAIR</span>
                                    @elseif ($mold->status == 'PREVENTIVE')
                                    <span class="badge badge-warning">PREVENTIVE</span>
                                    @else
                                    <span class="badge badge-secondary">{{ ucfirst($mold->status ?? '-') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                            class="btn btn-sm btn-warning editBtn"
                                            title="Edit"
                                            data-id="{{ $mold->id }}"
                                            data-no_mold="{{ $mold->no_mold }}"
                                            data-nama_mold="{{ $mold->nama_mold }}"
                                            data-type="{{ $mold->type }}"
                                            data-customer="{{ $mold->customer }}"
                                            data-status="{{ $mold->status }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('moldshop.destroy', $mold->id) }}"
                                            method="POST"
                                            style="display:inline;"
                                            class="deleteForm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination removed (handled by DataTables) --}}
            </div>
        </div>
    </div>
</div>

{{-- Include modals --}}
@include('moldshop.create')
@include('moldshop.update')
@endsection

@push('scripts')
<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

@if(session('success'))
<div id="success-message" data-message="{{ session('success') }}" style="display:none;"></div>
@endif
@endpush