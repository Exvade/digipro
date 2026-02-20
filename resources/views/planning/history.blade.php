@extends('planning.app')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                         Riwayat Planning Produksi
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
            <button class="btn btn-success" data-toggle="modal" data-target="#modalCetak">
                <i class="fas fa-print"></i> Cetak Planning
            </button>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                         Filter Data
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('planning.history') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="tanggal" class="mr-2">Filter Tanggal:</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" 
                                   value="{{ request('tanggal', date('Y-m-d', strtotime('-1 day'))) }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('planning.history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsHistory" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab-history" href="#" data-bagian="all">
                                <i class="fas fa-list"></i> All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inject-tab-history" href="#" data-bagian="inject">
                                <i class="fas fa-cog"></i> Inject
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="assy-tab-history" href="#" data-bagian="assy">
                                <i class="fas fa-tools"></i> Assy
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
                        <table id="historyTable" class="table table-striped table-bordered table-hover align-middle">
                            <thead class="text-center">
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>No Part</th>
                                    <th>Nama Part</th>
                                    <th>Bagian</th>
                                    <th>Mesin/Meja</th>
                                    <th style="width:150px;">Qty Planning</th>
                                    <th style="width:180px;">Aksi</th>
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
                                            {{-- Tombol Detail --}}
                                            <button class="btn btn-info btn-sm showBtn" title="Lihat Detail"
                                                data-id="{{ $planning->id }}"
                                                data-bagian="{{ $planning->bagian ?? 'inject' }}"
                                                data-no_mold="{{ $planning->moldshop->no_mold ?? '-' }}"
                                                data-mesin="{{ $planning->mesin->nama_mesin ?? '-' }}"
                                                data-meja="{{ $planning->meja->nama_meja ?? '-' }}"
                                                data-tanggal="{{ $planning->tanggal }}" 
                                                data-lot="{{ $planning->lot_produksi }}"
                                                data-no_part="{{ $planning->part->no_parts ?? '-' }}"
                                                data-nama_part="{{ $planning->part->nama_part ?? '-' }}"
                                                data-type="{{ $planning->part->type ?? '-' }}"
                                                data-qty="{{ number_format($planning->qty_planning_produksi) }}"
                                                data-status_approval="{{ $planning->status_approval ?? 'pending' }}"
                                                data-catatan="{{ $planning->catatan ?? '' }}"
                                                data-target_shift_pagi="{{ $planning->target_shift_pagi ?? 0 }}"
                                                data-target_shift_siang="{{ $planning->target_shift_siang ?? 0 }}"
                                                data-target_shift_malam="{{ $planning->target_shift_malam ?? 0 }}"
                                                data-total_shift="{{ $planning->total_shift ?? 0 }}"
                                                data-jam='@json(isset($planning->jam) ? $planning->jam->map(fn($j) => ['shift' => $j->shift, 'jam' => $j->jam, 'target' => $j->target]) : [])'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($plannings->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $plannings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- === MODALS === --}}
    @include('planning.show')
    
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
                                <option value="all">📋 Semua (Inject + Assy)</option>
                                <option value="inject">⚙️ Inject Saja</option>
                                <option value="assy">🔧 Assy Saja</option>
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
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/planning.css') }}">
@endpush

@push('scripts')
    <script>
        window.successMessage = @json(session('success'));
    </script>
    <script src="{{ asset('js/planning.js') }}"></script>
    <script src="{{ asset('js/planning-history.js') }}"></script>
@endpush