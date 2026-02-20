@extends('jadwal-kerja.app')

@section('title', 'Jadwal Kerja - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<!-- Custom Jadwal Kerja CSS (includes dark mode styles) -->
<link rel="stylesheet" href="{{ asset('css/jadwal-kerja.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Jadwal Kerja
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@php
$restrictedRoles = ['leader', 'foreman', 'operator'];
@endphp

{{-- Action Button --}}
@if (in_array(auth()->user()->role, ['super_admin', 'staff', 'manager_plant', 'kabag']))
<div class="row mb-3">
    <div class="col-12 text-right">
        <button class="btn btn-success mr-2" data-toggle="modal" data-target="#importExcelModal">
            <i class="fas fa-file-excel"></i> Import Excel
        </button>
        <button id="tambahJadwalBtn" class="btn btn-primary" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </button>
    </div>
</div>
@endif

{{-- Filter hanya untuk user non-produksi --}}
@if (!in_array(auth()->user()->role, $restrictedRoles))
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    Filter Data
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('jadwal-kerja.index') }}">
                    <div class="row">
                        <!-- Tanggal -->
                        <div class="form-group col-md-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                        </div>

                        <!-- Shift -->
                        <div class="form-group col-md-3">
                            <label>Shift</label>
                            <select name="shift" class="form-control">
                                <option value="">Semua Shift</option>
                                @foreach (['pagi', 'siang', 'malam', 'off'] as $s)
                                <option value="{{ $s }}" {{ request('shift') == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bagian -->
                        <div class="form-group col-md-3">
                            <label>Bagian</label>
                            <select name="bagian" class="form-control">
                                <option value="">Semua Bagian</option>
                                @foreach (['inject', 'assy'] as $b)
                                <option value="{{ $b }}" {{ request('bagian') == $b ? 'selected' : '' }}>
                                    {{ ucfirst($b) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="form-group col-md-3 d-flex align-items-end">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('jadwal-kerja.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Table Section --}}
<div class="row">
    <div class="col-12" data-jadwal-kerja-show-url="{{ route('jadwal-kerja.show', ':id') }}">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Jadwal Kerja
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="jadwalKerjaTable" class="table table-striped table-bordered table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Karyawan</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Jam Kerja</th>
                                <th>Bagian</th>
                                <th>Status</th>
                                <th style="width:150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $startIndex =
                            isset($jadwalKerjas) && method_exists($jadwalKerjas, 'firstItem')
                            ? $jadwalKerjas->firstItem()
                            : 1;
                            @endphp

                            @foreach ($jadwalKerjas as $index => $jadwal)
                            <tr>
                                <td class="text-center">{{ $startIndex + $index }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $jadwal->user->nama ?? '-' }}</div>
                                    <small class="text-muted">{{ ucfirst($jadwal->user->role ?? '-') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd') }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                    $shiftColors = [
                                    'pagi' => 'badge-success',
                                    'siang' => 'badge-warning',
                                    'malam' => 'badge-info',
                                    'off' => 'badge-secondary',
                                    ];
                                    @endphp
                                    <span class="badge {{ $shiftColors[$jadwal->shift] ?? 'badge-secondary' }}">
                                        {{ ucfirst($jadwal->shift) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($jadwal->shift !== 'off')
                                    <div class="font-weight-bold">{{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}</div>
                                    <small class="text-muted">{{ $jadwal->durasi_kerja }} jam</small>
                                    @else
                                    <span class="text-muted">Libur</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light text-dark">
                                        {{ $jadwal->user ? ucfirst($jadwal->user->bagian) : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $jadwal->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($jadwal->status ?? '-') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Detail -->
                                    <button type="button" class="btn btn-sm btn-info btn-detail mr-1"
                                        data-id="{{ $jadwal->id }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if (in_array(auth()->user()->role, ['staff', 'super_admin']))
                                    <!-- Edit -->
                                    <button type="button" class="btn btn-sm btn-warning mr-1 btn-edit"
                                        data-id="{{ $jadwal->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                        data-id="{{ $jadwal->id }}"
                                        data-nama="{{ $jadwal->user->nama ?? '-' }}"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($jadwalKerjas->hasPages())
                <div class="pagination-info-wrapper">
                    <div class="pagination-info">
                        Showing {{ $jadwalKerjas->firstItem() ?? 0 }} to {{ $jadwalKerjas->lastItem() ?? 0 }} of {{ $jadwalKerjas->total() }} entries
                    </div>
                    <div class="pagination-wrapper">
                        {{ $jadwalKerjas->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                @else
                <div class="pagination-info-wrapper">
                    <div class="pagination-info">
                        Showing {{ $jadwalKerjas->count() }} entries
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="detailModalLabel">Detail Jadwal Kerja</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center text-muted py-3">Memuat data...</div>
            </div>
        </div>
    </div>
</div>

{{-- === MODALS === --}}
@include('jadwal-kerja.create')
@include('jadwal-kerja.update')
@include('jadwal-kerja.import-excel')

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="detailModalLabel">Detail Jadwal Kerja</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center text-muted py-3">Memuat data...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS (includes dark mode functionality) -->
<script src="{{ asset('js/jadwal-kerja.js') }}"></script>
@endpush