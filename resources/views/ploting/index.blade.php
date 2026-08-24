@extends('layouts.app')

@section('title', 'Plooting Produksi - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Ploting CSS -->
<link rel="stylesheet" href="{{ asset('css/ploting.css') }}">
<!-- Planning CSS for Tab Filter -->
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Plooting Produksi
                </h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Filter Tanggal dan Shift --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-info filter-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filter Data
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal Produksi</label>
                            <div class="d-flex align-items-center">
                                <select class="form-control" id="filterTanggalMode" style="width:auto;">
                                    <option value="today">Hari Ini ({{ date('d-m-Y') }})</option>
                                    <option value="tomorrow">Besok ({{ \Carbon\Carbon::tomorrow()->format('d-m-Y') }})</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <input type="date" class="form-control ml-2" id="filterTanggal" value="{{ request('tanggal', date('Y-m-d')) }}" style="width:auto; display:none;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Filter Shift (Opsional)</label>
                            <select class="form-control" id="filterShift">
                                <option value="">Semua Shift</option>
                                <option value="pagi">Shift Pagi</option>
                                <option value="siang">Shift Siang</option>
                                <option value="malam">Shift Malam</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Filter operator berdasarkan shift dari jadwal kerja
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">&nbsp;</label>
                            <form method="GET" action="{{ route('ploting.index') }}" class="d-flex align-items-center" id="plotingFilterForm">
                                <input type="hidden" name="tanggal" id="tanggalParam" value="{{ request('tanggal', date('Y-m-d')) }}">
                                <button class="btn btn-primary btn-block" id="applyFilter" type="submit" style="max-width: 200px;">
                                    <i class="fas fa-filter mr-1"></i>Terapkan Filter
                                </button>
                                @if(request('tanggal'))
                                <span class="badge badge-success ml-2" id="filterStatus">
                                    <i class="fas fa-check-circle mr-1"></i>Filter Aktif
                                </span>
                                @else
                                <span class="badge badge-success ml-2" id="filterStatus" style="display: none;">
                                    <i class="fas fa-check-circle mr-1"></i>Filter Aktif
                                </span>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Ploting H-1:</strong> Sistem ini dirancang untuk ploting H-1 (hari sebelumnya). Ketika Anda memilih grup, operator akan otomatis dimuat dari grup tersebut dan shift akan otomatis ditentukan berdasarkan jadwal kerja operator pada tanggal yang dipilih.
                        <br><small class="text-muted"><strong>Tips:</strong> Untuk ploting besok, pilih tanggal besok di filter tanggal. Operator yang ditampilkan akan sesuai dengan jadwal kerja mereka untuk tanggal tersebut.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- {{-- Tab Filter Section -- Hanya tampilkan jika bukan leader dengan bagian spesifik --}}
@if(!isset($isLeaderInject) || !$isLeaderInject)
@if(!isset($isLeaderAssy) || !$isLeaderAssy)
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsPloting" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab-ploting" href="#" data-bagian="all">
                            <i class="fas fa-list"></i> All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inject-tab-ploting" href="#" data-bagian="inject">
                            <i class="fas fa-cog"></i> Inject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assy-tab-ploting" href="#" data-bagian="assy">
                            <i class="fas fa-tools"></i> Assy
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif
@endif -->

{{-- Table Section untuk INJECT --}}
@if($planningsInject->count() > 0)
<div class="row ploting-section" id="inject-section" @if(isset($isLeaderInject) && $isLeaderInject) style="display: block;" @elseif(isset($isLeaderAssy) && $isLeaderAssy) style="display: none;" @endif>
    <div class="col-12">
        <div class="card card-outline card-primary table-card">
            <div class="card-header bg-primary">
                <h3 class="card-title text-white">
                    <i class="fas fa-table"></i> Daftar Plooting Produksi - INJECT
                </h3>
            </div>
            <div class="card-body">
                {{-- DataTables akan otomatis menambahkan: Search box, Length menu, Info, dan Pagination --}}
                <div class="table-responsive">
                    <table id="plotingTableInject" class="table table-striped table-bordered table-hover align-middle plotingTable" style="width:100%">
                        <thead class="text-center">
                            <tr>
                                <th style="width:120px;">Tanggal</th>
                                <th>Part</th>
                                <th>Mesin</th>
                                <th class="text-center" style="width:100px;">Qty</th>
                                <th style="min-width:600px;">Detail Plooting</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planningsInject as $planning)
                            <tr>
                                <td class="text-center">
                                    <div class="font-weight-bold">{{ optional($planning->tanggal)->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ optional($planning->tanggal)->isoFormat('dddd') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $planning->part->nama_part ?? '-' }}</div>
                                    <button class="btn btn-info btn-xs mt-1 showBtn"
                                        title="Lihat Detail Planning"
                                        data-id="{{ $planning->id }}"
                                        data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                        data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                        data-tanggal="{{ optional($planning->tanggal)->format('d-m-Y') }}"
                                        data-lot="{{ $planning->lot_produksi }}"
                                        data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                        data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                        data-type="{{ $planning->part->type ?? '-' }}"
                                        data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                        data-status="{{ $planning->status }}"
                                        data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                        data-operators='@json($planning->plotings->map(fn($p) => [
                                        'nama' => $p->operator->nama ?? '-',
                                        'grup' => $p->grup->nama_grup ?? '-',
                                        'status' => $p->status ?? 'Belum di-assign'
                                        ]))'>
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                                <td>{{ $planning->mesin->nama_mesin ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ number_format($planning->qty_planning_produksi ?? 0) }}</span>
                                </td>
                                <td>
                                    {{-- Nested Table untuk Detail Operator --}}
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width:100px;">Grup</th>
                                                    <th style="min-width:250px;">Operator</th>
                                                    <th class="text-center" style="width:100px;">Shift</th>
                                                    <th class="text-center" style="width:120px;">Status</th>
                                                    <th class="text-center" style="width:120px;">Run Time</th>
                                                    <th class="text-center" style="width:100px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($planning->plotings as $ploting)
                                                <tr>
                                                    {{-- Kolom Grup --}}
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary">{{ $ploting->grup->nama_grup ?? '-' }}</span>
                                                    </td>

                                                    {{-- Kolom Operator --}}
                                                    <td>
                                                        @if (!$ploting->user_id)
                                                        {{-- Belum ada operator --}}
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="form-inline justify-content-center">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="inject">

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="submit" class="btn btn-primary btn-sm" title="Tambah Operator">
                                                                <i class="fas fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                        @elseif (in_array($ploting->status, [null, 'waiting']))
                                                        {{-- Bisa update --}}
                                                        <form action="{{ route('ploting.update', $ploting->id) }}"
                                                            method="POST"
                                                            class="form-inline justify-content-center update-form"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}">
                                                            @csrf
                                                            @method('PUT')

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}"
                                                                    {{ $ploting->grup_id == $grup->id ? 'selected' : '' }}>
                                                                    {{ $grup->nama_grup }}
                                                                </option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;"
                                                                data-current-user="{{ $ploting->user_id }}" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="btn btn-warning btn-sm update-btn" title="Update Operator">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </form>
                                                        @else
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <strong>{{ $ploting->operator->nama ?? '-' }}</strong>
                                                            <i class="fas fa-lock ml-2 text-muted" data-toggle="tooltip"
                                                                title="Tidak dapat diubah karena produksi sudah {{ $ploting->status == 'on_progress' ? 'dimulai' : 'selesai' }}"></i>
                                                        </div>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Shift --}}
                                                    <td class="text-center">
                                                        @php
                                                        $operatorShift = null;
                                                        if ($ploting->operator) {
                                                        $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                        ->whereDate('tanggal', $tanggalPlotting)
                                                        ->where('status', 'aktif')
                                                        ->first();
                                                        if ($jadwal) {
                                                        $operatorShift = $jadwal->shift;
                                                        }
                                                        }
                                                        @endphp
                                                        @if ($operatorShift && $operatorShift !== 'off')
                                                        @php
                                                        $shiftColors = [
                                                        'pagi' => 'badge-success',
                                                        'siang' => 'badge-warning',
                                                        'malam' => 'badge-info',
                                                        ];
                                                        @endphp
                                                        <span class="badge {{ $shiftColors[$operatorShift] ?? 'badge-secondary' }}">
                                                            Shift {{ ucfirst($operatorShift) }}
                                                        </span>
                                                        @else
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Status --}}
                                                    <td class="text-center">
                                                        @switch($ploting->status)
                                                        @case('waiting')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Waiting
                                                        </span>
                                                        @break
                                                        @case('on_progress')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-play mr-1"></i>On Progress
                                                        </span>
                                                        @break
                                                        @case('done')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>Done
                                                        </span>
                                                        @break
                                                        @default
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-user-slash mr-1"></i>Belum di-assign
                                                        </span>
                                                        @endswitch
                                                    </td>

                                                    {{-- Kolom Run Time --}}
                                                    <td class="text-center">
                                                        @if ($ploting->status === 'done' && $ploting->run_time)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-hourglass-end mr-1"></i>
                                                            {{ gmdate('H:i:s', $ploting->run_time) }}
                                                        </span>
                                                        @elseif ($ploting->status === 'on_progress')
                                                        @php
                                                        // Hitung shift end time dari jadwal kerja operator
                                                        $shiftEndTime = null;
                                                        if ($ploting->operator) {
                                                        $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                        ->whereDate('tanggal', $tanggalPlotting)
                                                        ->where('status', 'aktif')
                                                        ->first();

                                                        if ($jadwal && $jadwal->jam_keluar) {
                                                        try {
                                                        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                                                        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                                                        } catch (\Exception $e) {
                                                        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                                                        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                                                        }

                                                        // Jika shift malam (jam keluar < jam masuk), tambahkan 1 hari
                                                            if ($jamKeluar->lessThan($jamMasuk)) {
                                                            $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                                                            } else {
                                                            $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                                                            }
                                                            }
                                                            }
                                                            @endphp
                                                            <span class="badge badge-info" id="runtime-{{ $ploting->id }}"
                                                                data-ploting-id="{{ $ploting->id }}"
                                                                data-start-time="{{ $ploting->start_time }}"
                                                                data-shift-end-time="{{ $shiftEndTime ? $shiftEndTime->format('Y-m-d H:i:s') : '' }}">
                                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                                <span class="runtime-display">Berjalan...</span>
                                                            </span>
                                                            @else
                                                            <span class="text-muted">-</span>
                                                            @endif
                                                    </td>

                                                    {{-- Kolom Aksi --}}
                                                    <td class="text-center">
                                                        @if ($ploting->user_id)
                                                        @if ($ploting->status === null || $ploting->status === 'waiting')
                                                        @php
                                                        $missingDocs = $planning->mesin ? $planning->mesin->getMissingDocuments() : ['Semua Dokumen'];
                                                        $isDocComplete = $planning->mesin && count($missingDocs) === 0;
                                                        @endphp
                                                        <button type="button" class="btn btn-info btn-sm start-btn"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            data-doc-complete="{{ $isDocComplete ? 'true' : 'false' }}"
                                                            data-missing-docs="{{ implode(', ', $missingDocs) }}"
                                                            title="Start Produksi">
                                                            <i class="fas fa-play mr-1"></i>Start
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'on_progress')
                                                        <button type="button" class="btn btn-success btn-sm finish-btn"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            title="Finish Produksi">
                                                            <i class="fas fa-stop mr-1"></i>Finish
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'done')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Selesai
                                                        </span>
                                                        @endif
                                                        @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle mr-1"></i>Assign dulu
                                                        </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-2">
                                                        <i class="fas fa-users-slash mr-2"></i>Belum ada operator
                                                    </td>
                                                </tr>
                                                @endforelse

                                                {{-- Form tambah operator baru --}}
                                                <tr class="bg-light">
                                                    <td colspan="6">
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="form-inline justify-content-center add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="inject">

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Pilih Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Pilih Operator</option>
                                                            </select>

                                                            <button type="button" class="btn btn-success btn-sm add-operator-btn" title="Tambah Operator">
                                                                <i class="fas fa-plus mr-1"></i>Tambah
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard-list mr-2"></i>Tidak ada planning produksi
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
@endif

{{-- Table Section untuk ASSEMBLY --}}
@if($planningsAssy->count() > 0)
<div class="row mt-4 ploting-section" id="assy-section" @if(isset($isLeaderInject) && $isLeaderInject) style="display: none;" @elseif(isset($isLeaderAssy) && $isLeaderAssy) style="display: block;" @endif>
    <div class="col-12">
        <div class="card card-outline card-info table-card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">
                    <i class="fas fa-table"></i> Daftar Plooting Produksi - ASSEMBLY
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="plotingTableAssy" class="table table-striped table-bordered table-hover align-middle plotingTable" style="width:100%">
                        <thead class="text-center">
                            <tr>
                                <th style="width:120px;">Tanggal</th>
                                <th>Part</th>
                                <th>Meja</th>
                                <th class="text-center" style="width:100px;">Qty</th>
                                <th style="min-width:600px;">Detail Plooting</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planningsAssy as $planning)
                            <tr>
                                <td class="text-center">
                                    <div class="font-weight-bold">{{ optional($planning->tanggal)->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ optional($planning->tanggal)->isoFormat('dddd') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $planning->part->nama_part ?? '-' }}</div>
                                    <button class="btn btn-info btn-xs mt-1 showBtn"
                                        title="Lihat Detail Planning"
                                        data-id="{{ $planning->id }}"
                                        data-bagian="assy"
                                        data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                        data-tanggal="{{ optional($planning->tanggal)->format('d-m-Y') }}"
                                        data-lot="{{ $planning->lot_produksi }}"
                                        data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                        data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                        data-type="{{ $planning->part->type ?? '-' }}"
                                        data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                        data-status="{{ $planning->status }}"
                                        data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                        data-catatan="{{ $planning->catatan ?? '' }}"
                                        data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                        data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                        data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                        data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                        data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => [' shift'=> $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'
                                        data-operators='@json($planning->plotings->map(fn($p) => [
                                        'nama' => $p->operator->nama ?? '-',
                                        'grup' => $p->grup->nama_grup ?? '-',
                                        'status' => $p->status ?? 'Belum di-assign'
                                        ]))'>
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                                <td>{{ $planning->meja->nama_meja ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ number_format($planning->qty_planning_produksi ?? 0) }}</span>
                                </td>
                                <td>
                                    {{-- Nested Table untuk Detail Operator --}}
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width:100px;">Grup</th>
                                                    <th style="min-width:250px;">Operator</th>
                                                    <th class="text-center" style="width:100px;">Shift</th>
                                                    <th class="text-center" style="width:120px;">Status</th>
                                                    <th class="text-center" style="width:120px;">Run Time</th>
                                                    <th class="text-center" style="width:100px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($planning->plotings as $ploting)
                                                <tr>
                                                    {{-- Kolom Grup --}}
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary">{{ $ploting->grup->nama_grup ?? '-' }}</span>
                                                    </td>

                                                    {{-- Kolom Operator --}}
                                                    <td>
                                                        @if (!$ploting->user_id)
                                                        {{-- Belum ada operator --}}
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="form-inline justify-content-center">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="submit" class="btn btn-primary btn-sm" title="Tambah Operator">
                                                                <i class="fas fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                        @elseif (in_array($ploting->status, [null, 'waiting']))
                                                        {{-- Bisa update --}}
                                                        <form action="{{ route('ploting.update', $ploting->id) }}"
                                                            method="POST"
                                                            class="form-inline justify-content-center update-form"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}"
                                                                    {{ $ploting->grup_id == $grup->id ? 'selected' : '' }}>
                                                                    {{ $grup->nama_grup }}
                                                                </option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;"
                                                                data-current-user="{{ $ploting->user_id }}" required>
                                                                <option value="">Operator</option>
                                                            </select>

                                                            <button type="button" class="btn btn-warning btn-sm update-btn" title="Update Operator">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </form>
                                                        @else
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <strong>{{ $ploting->operator->nama ?? '-' }}</strong>
                                                            <i class="fas fa-lock ml-2 text-muted" data-toggle="tooltip"
                                                                title="Tidak dapat diubah karena produksi sudah {{ $ploting->status == 'on_progress' ? 'dimulai' : 'selesai' }}"></i>
                                                        </div>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Shift --}}
                                                    <td class="text-center">
                                                        @php
                                                        $operatorShift = null;
                                                        if ($ploting->operator) {
                                                        $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                        ->whereDate('tanggal', $tanggalPlotting)
                                                        ->where('status', 'aktif')
                                                        ->first();
                                                        if ($jadwal) {
                                                        $operatorShift = $jadwal->shift;
                                                        }
                                                        }
                                                        @endphp
                                                        @if ($operatorShift && $operatorShift !== 'off')
                                                        @php
                                                        $shiftColors = [
                                                        'pagi' => 'badge-success',
                                                        'siang' => 'badge-warning',
                                                        'malam' => 'badge-info',
                                                        ];
                                                        @endphp
                                                        <span class="badge {{ $shiftColors[$operatorShift] ?? 'badge-secondary' }}">
                                                            Shift {{ ucfirst($operatorShift) }}
                                                        </span>
                                                        @else
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Status (sama seperti inject) --}}
                                                    <td class="text-center">
                                                        @switch($ploting->status)
                                                        @case('waiting')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Waiting
                                                        </span>
                                                        @break
                                                        @case('on_progress')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-play mr-1"></i>On Progress
                                                        </span>
                                                        @break
                                                        @case('done')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>Done
                                                        </span>
                                                        @break
                                                        @default
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-user-slash mr-1"></i>Belum di-assign
                                                        </span>
                                                        @endswitch
                                                    </td>

                                                    {{-- Kolom Run Time (sama seperti inject) --}}
                                                    <td class="text-center">
                                                        @if ($ploting->status === 'done' && $ploting->run_time)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-hourglass-end mr-1"></i>
                                                            {{ gmdate('H:i:s', $ploting->run_time) }}
                                                        </span>
                                                        @elseif ($ploting->status === 'on_progress')
                                                        @php
                                                        $shiftEndTime = null;
                                                        if ($ploting->operator) {
                                                        $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : $filterTanggal;
                                                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                                                        ->whereDate('tanggal', $tanggalPlotting)
                                                        ->where('status', 'aktif')
                                                        ->first();

                                                        if ($jadwal && $jadwal->jam_keluar) {
                                                        try {
                                                        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                                                        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                                                        } catch (\Exception $e) {
                                                        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                                                        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                                                        }

                                                        if ($jamKeluar->lessThan($jamMasuk)) {
                                                        $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                                                        } else {
                                                        $shiftEndTime = \Carbon\Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                                                        }
                                                        }
                                                        }
                                                        @endphp
                                                        <span class="badge badge-info" id="runtime-{{ $ploting->id }}"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-start-time="{{ $ploting->start_time }}"
                                                            data-shift-end-time="{{ $shiftEndTime ? $shiftEndTime->format('Y-m-d H:i:s') : '' }}">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                                            <span class="runtime-display">Berjalan...</span>
                                                        </span>
                                                        @else
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Kolom Aksi (sama seperti inject) --}}
                                                    <td class="text-center">
                                                        @if ($ploting->user_id)
                                                        @if ($ploting->status === null || $ploting->status === 'waiting')
                                                        @php
                                                        $missingDocsAssy = $planning->meja ? $planning->meja->getMissingDocuments() : ['Semua Dokumen'];
                                                        $isDocCompleteAssy = $planning->meja && count($missingDocsAssy) === 0;
                                                        @endphp
                                                        <button type="button" class="btn btn-info btn-sm start-btn"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            data-doc-complete="{{ $isDocCompleteAssy ? 'true' : 'false' }}"
                                                            data-missing-docs="{{ implode(', ', $missingDocsAssy) }}"
                                                            title="Start Produksi">
                                                            <i class="fas fa-play mr-1"></i>Start
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'on_progress')
                                                        <button type="button" class="btn btn-success btn-sm finish-btn"
                                                            data-ploting-id="{{ $ploting->id }}"
                                                            data-operator-name="{{ $ploting->operator->nama ?? '' }}"
                                                            title="Finish Produksi">
                                                            <i class="fas fa-stop mr-1"></i>Finish
                                                        </button>
                                                        @endif

                                                        @if ($ploting->status === 'done')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Selesai
                                                        </span>
                                                        @endif
                                                        @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle mr-1"></i>Assign dulu
                                                        </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-2">
                                                        <i class="fas fa-users-slash mr-2"></i>Belum ada operator
                                                    </td>
                                                </tr>
                                                @endforelse

                                                {{-- Form tambah operator baru --}}
                                                <tr class="bg-light">
                                                    <td colspan="6">
                                                        <form action="{{ route('ploting.store') }}" method="POST"
                                                            class="form-inline justify-content-center add-operator-form">
                                                            @csrf
                                                            <input type="hidden" name="planning_id" value="{{ $planning->id }}">
                                                            <input type="hidden" class="bagian-input" value="assy">

                                                            <select name="grup_id"
                                                                class="form-control form-control-sm mr-1 grup-select"
                                                                style="width: 100px;" required>
                                                                <option value="">Pilih Grup</option>
                                                                @foreach ($grups as $grup)
                                                                <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                                                @endforeach
                                                            </select>

                                                            <select name="user_id"
                                                                class="form-control form-control-sm mr-1 operator-select"
                                                                style="width: 120px;" required>
                                                                <option value="">Pilih Operator</option>
                                                            </select>

                                                            <button type="button" class="btn btn-success btn-sm add-operator-btn" title="Tambah Operator">
                                                                <i class="fas fa-plus mr-1"></i>Tambah
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard-list mr-2"></i>Tidak ada planning produksi Assembly
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
@endif

{{-- Alert jika tidak ada data sama sekali --}}
@if($planningsInject->count() == 0 && $planningsAssy->count() == 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong style="color: #856404 !important;">Tidak ada planning produksi yang sudah di-approve untuk tanggal ini.</strong>
            <br><small style="color: #856404 !important;">Silakan pilih tanggal lain atau tunggu approval dari Kabag PPIC.</small>
        </div>
    </div>
</div>
@endif

{{-- Modal Konfirmasi --}}
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Aksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Konten akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

{{-- Include Modal Show Planning --}}
@include('planning.show')
@endsection

@push('scripts')
<!-- DataTables JS sudah di-load di layouts/app.blade.php, tidak perlu di-load lagi -->
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Set Laravel routes and variables for ploting.js
    window.plotingRoutes = {
        operatorsByGroup: "{{ route('ploting.operators') }}",
        plotingStart: "{{ url('ploting') }}/",
        plotingFinish: "{{ url('ploting') }}/",
        plotingRuntime: "{{ url('ploting') }}/",
        csrfToken: "{{ csrf_token() }}"
    };

    // Set variables for ploting-index.js
    window.plotingTodayYmd = "{{ date('Y-m-d') }}";
    window.plotingTomorrowYmd = "{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}";
    window.plotingCurrentYmd = "{{ request('tanggal', date('Y-m-d')) }}";
</script>


<!-- Custom Ploting Index JS -->
<script src="{{ asset('js/ploting-index.js') }}"
    data-is-leader-inject="{{ isset($isLeaderInject) && $isLeaderInject ? 'true' : 'false' }}"
    data-is-leader-assy="{{ isset($isLeaderAssy) && $isLeaderAssy ? 'true' : 'false' }}">
</script>

<!-- Custom Ploting JS -->
<script src="{{ asset('js/ploting.js') }}"></script>
@endpush