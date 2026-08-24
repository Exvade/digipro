@extends('user.app')

@section('title', 'Pengaturan Dokumen Mesin & Meja - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- Custom Admin Documents CSS -->
<link rel="stylesheet" href="{{ asset('css/documents.css') }}">
@endpush

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Dokumen Mesin 
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

{{-- Main Card --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Daftar Dokumen Mesin 
                </h3>
            </div>
            <div class="card-body">

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-mesin" role="tab">
                            <i class="fas fa-cogs mr-1"></i> Per Mesin ({{ $mesins->count() }})
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-meja" role="tab">
                            <i class="fas fa-table mr-1"></i> Per Meja ({{ $mejas->count() }})
                        </a>
                    </li> -->
                </ul>

                @php
                $types = [
                'apd' => ['label' => 'APD', 'icon' => 'fa-shield-alt', 'color' => 'primary'],
                'qpoint' => ['label' => 'Q-Point', 'icon' => 'fa-award', 'color' => 'success'],
                'instruksi_kerja' => ['label' => 'Instruksi Kerja', 'icon' => 'fa-file-alt', 'color' => 'info'],
                'limit_sampel' => ['label' => 'Limit Sampel', 'icon' => 'fa-chart-bar', 'color' => 'warning'],
                'amptm' => ['label' => 'AMTPM', 'icon' => 'fa-clipboard-list', 'color' => 'danger']
                ];
                @endphp

                <div class="tab-content">
                    {{-- ======================== TAB MESIN ======================== --}}
                    <div class="tab-pane fade show active" id="tab-mesin" role="tabpanel">
                        <div class="table-responsive">
                            <table id="mesin-table" class="table doc-table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 200px; min-width: 200px; max-width: 200px;">
                                            <i class="fas fa-cogs mr-1"></i> Nama Mesin
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-shield-alt mr-1"></i> APD
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-award mr-1"></i> Q-Point
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-file-alt mr-1"></i> Instruksi Kerja
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-chart-bar mr-1"></i> Limit Sampel
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-clipboard-list mr-1"></i> AMTPM
                                        </th>
                                        <th style="width: 280px; min-width: 280px; max-width: 280px;">
                                            <i class="fas fa-upload mr-1"></i> Upload Dokumen
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mesins as $mesin)
                                    @php
                                    // Upload is ONLY allowed if status is 'running'
                                    $isDisabled = ($mesin->status !== 'running');

                                    $statusMessage = '';
                                    if ($mesin->status === 'maintenance') {
                                    $statusMessage = 'Sedang Breakdown / Perbaikan';
                                    } elseif ($mesin->status === 'offline') {
                                    $statusMessage = 'Mesin OFF / Tidak Ada Planning';
                                    } elseif ($mesin->status === 'stop') {
                                    $statusMessage = 'Mesin Stand By';
                                    }

                                    // Ambil upcoming parts dengan safe check
                                    $upcomingParts = collect();
                                    if (isset($mesin->plannings) && $mesin->plannings) {
                                    $upcomingParts = $mesin->plannings->filter(function($p) {
                                    return isset($p->tanggal) && $p->tanggal >= now()->format('Y-m-d');
                                    })->take(5);
                                    }
                                    @endphp
                                    <tr class="{{ $isDisabled ? 'machine-disabled' : '' }}">
                                        <td>
                                            <div>
                                                <strong class="d-block">{{ $mesin->nama_mesin }}</strong>
                                                @if($mesin->brand_mesin)
                                                <small class="text-muted d-block">{{ $mesin->brand_mesin }}</small>
                                                @endif

                                                {{-- Status Display Matching Mesin Index --}}
                                                <div class="mt-1">
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
                                                </div>

                                                @if($isDisabled)
                                                <div class="alert alert-warning alert-sm mt-2 mb-0">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    <small><strong>{{ $statusMessage }}</strong> - Tidak dapat mengupload dokumen</small>
                                                </div>
                                                @endif

                                                {{-- Informasi Part yang Akan Dikerjakan --}}
                                                @if($upcomingParts && $upcomingParts->count() > 0)
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="fas fa-clipboard-list mr-1"></i><strong>Part Mendatang:</strong>
                                                    </small>
                                                    <div class="part-list">
                                                        @foreach($upcomingParts as $planning)
                                                        @if($planning->part)
                                                        <div class="part-item">
                                                            <span class="badge badge-info badge-sm d-inline-block">
                                                                {{ $planning->part->no_parts ?? '-' }}
                                                            </span>
                                                            <small class="text-dark d-block">
                                                                {{ \Illuminate\Support\Str::limit($planning->part->nama_part ?? '-', 30) }}
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                                @if(isset($planning->tanggal))
                                                                {{ \Carbon\Carbon::parse($planning->tanggal)->format('d/m/Y') }}
                                                                @endif
                                                                @if(isset($planning->qty_planning_produksi) && $planning->qty_planning_produksi)
                                                                | Qty: {{ number_format($planning->qty_planning_produksi) }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                        @endif
                                                        @endforeach
                                                        @php
                                                        $totalUpcoming = 0;
                                                        if (isset($mesin->plannings) && $mesin->plannings) {
                                                        $totalUpcoming = $mesin->plannings->filter(function($p) {
                                                        return isset($p->tanggal) && $p->tanggal >= now()->format('Y-m-d');
                                                        })->count();
                                                        }
                                                        @endphp
                                                        @if($totalUpcoming > 5)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-ellipsis-h mr-1"></i> +{{ $totalUpcoming - 5 }} part lainnya
                                                        </small>
                                                        @endif
                                                    </div>
                                                </div>
                                                @else
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>Belum ada planning part
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </td>

                                        @foreach ($types as $key => $typeInfo)
                                        @php
                                        $doc = null;
                                        if (method_exists($mesin, 'getDocument')) {
                                        $doc = $mesin->getDocument($key);
                                        }
                                        @endphp
                                        <td class="text-center">
                                            @if ($doc)
                                            <span class="doc-badge has-doc d-block" title="{{ $doc->filename ?? 'Tersedia' }}">
                                                <i class="fas fa-check-circle mr-1"></i> Ada
                                            </span>
                                            @if($doc->filename)
                                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;" title="{{ $doc->filename }}">
                                                {{ \Illuminate\Support\Str::limit($doc->filename, 15) }}
                                            </small>
                                            @endif
                                            <button type="button"
                                                class="btn btn-{{ $typeInfo['color'] }} btn-sm doc-action-btn mt-1 btn-view-document"
                                                data-doc-url="{{ asset($doc->url) }}"
                                                data-doc-name="{{ $doc->filename ?? $typeInfo['label'] }}"
                                                title="Lihat Dokumen: {{ $doc->filename ?? 'Dokumen' }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </button>
                                            @else
                                            <span class="doc-badge no-doc">
                                                <i class="fas fa-times-circle mr-1"></i> Tidak Ada
                                            </span>
                                            @endif
                                        </td>
                                        @endforeach

                                        <td class="upload-column">
                                            @if($isDisabled)
                                            <div class="alert alert-danger alert-sm upload-alert-disabled mb-0">
                                                <div>
                                                    <i class="fas fa-ban mb-2" style="font-size: 1.5rem;"></i>
                                                    <small>
                                                        <strong>Upload Terkunci</strong><br>
                                                        Upload dokumen hanya diizinkan saat status<br>
                                                        <strong>RUNNING</strong>.
                                                    </small>
                                                </div>
                                            </div>
                                            @else
                                            <form action="{{ route('documents.upload.mesin', $mesin->id) }}"
                                                method="POST" enctype="multipart/form-data" class="upload-form">
                                                @csrf
                                                <div class="form-group mb-2">
                                                    <select name="type" class="form-control form-control-sm" required>
                                                        <option value="">Pilih Jenis Dokumen</option>
                                                        @foreach ($types as $key => $typeInfo)
                                                        <option value="{{ $key }}">{{ $typeInfo['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <div class="custom-file">
                                                        <input type="file" name="dokumen"
                                                            class="custom-file-input"
                                                            id="file-mesin-{{ $mesin->id }}"
                                                            accept=".pdf,.doc,.docx"
                                                            required>
                                                        <label class="custom-file-label" for="file-mesin-{{ $mesin->id }}">
                                                            Pilih file...
                                                        </label>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                                    <i class="fas fa-upload mr-1"></i> Upload
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ======================== TAB MEJA ======================== --}}
                    <div class="tab-pane fade" id="tab-meja" role="tabpanel">
                        <div class="table-responsive">
                            <table id="meja-table" class="table doc-table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 200px; min-width: 200px; max-width: 200px;">
                                            <i class="fas fa-table mr-1"></i> Nama Meja
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-shield-alt mr-1"></i> APD
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-award mr-1"></i> Q-Point
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-file-alt mr-1"></i> Instruksi Kerja
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-chart-bar mr-1"></i> Limit Sampel
                                        </th>
                                        <th style="width: 120px; min-width: 120px; max-width: 120px;">
                                            <i class="fas fa-clipboard-list mr-1"></i> AMTPM
                                        </th>
                                        <th style="width: 280px; min-width: 280px; max-width: 280px;">
                                            <i class="fas fa-upload mr-1"></i> Upload Dokumen
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mejas as $meja)
                                    @php
                                    $isMejaDisabled = $meja->status === 'tidak_aktif';
                                    $mejaStatusClass = str_replace('_', '-', strtolower($meja->status));
                                    @endphp
                                    <tr class="{{ $isMejaDisabled ? 'machine-disabled' : '' }}">
                                        <td>
                                            <div>
                                                <strong class="d-block">{{ $meja->nama_meja }}</strong>
                                                @if($meja->lokasi)
                                                <small class="text-muted d-block">{{ $meja->lokasi }}</small>
                                                @endif

                                                <span class="status-badge {{ $mejaStatusClass }} d-inline-block mt-1">
                                                    <i class="fas fa-{{ $meja->status === 'aktif' ? 'check-circle' : 'ban' }} mr-1"></i>
                                                    {{ str_replace('_', ' ', ucfirst($meja->status)) }}
                                                </span>

                                                @if($isMejaDisabled)
                                                <div class="alert alert-warning alert-sm mt-2 mb-0">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    <small><strong>Meja Tidak Aktif</strong> - Tidak dapat mengupload dokumen</small>
                                                </div>
                                                @endif
                                            </div>
                                        </td>

                                        @foreach ($types as $key => $typeInfo)
                                        @php
                                        $doc = null;
                                        if (method_exists($meja, 'getDocument')) {
                                        $doc = $meja->getDocument($key);
                                        }
                                        @endphp
                                        <td class="text-center">
                                            @if ($doc)
                                            <span class="doc-badge has-doc d-block" title="{{ $doc->filename ?? 'Tersedia' }}">
                                                <i class="fas fa-check-circle mr-1"></i> Ada
                                            </span>
                                            @if($doc->filename)
                                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;" title="{{ $doc->filename }}">
                                                {{ \Illuminate\Support\Str::limit($doc->filename, 15) }}
                                            </small>
                                            @endif
                                            <button type="button"
                                                class="btn btn-{{ $typeInfo['color'] }} btn-sm doc-action-btn mt-1 btn-view-document"
                                                data-doc-url="{{ asset($doc->url) }}"
                                                data-doc-name="{{ $doc->filename ?? $typeInfo['label'] }}"
                                                title="Lihat Dokumen: {{ $doc->filename ?? 'Dokumen' }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </button>
                                            @else
                                            <span class="doc-badge no-doc">
                                                <i class="fas fa-times-circle mr-1"></i> Tidak Ada
                                            </span>
                                            @endif
                                        </td>
                                        @endforeach

                                        <td class="upload-column">
                                            @if($isMejaDisabled)
                                            <div class="alert alert-danger alert-sm upload-alert-disabled mb-0">
                                                <div>
                                                    <i class="fas fa-ban mb-2" style="font-size: 1.5rem;"></i>
                                                    <small>
                                                        <strong>Upload Dibatalkan</strong><br>
                                                        Meja dengan status<br>
                                                        <strong>Tidak Aktif</strong><br>
                                                        tidak dapat mengupload dokumen.
                                                    </small>
                                                </div>
                                            </div>
                                            @else
                                            <form action="{{ route('documents.upload.meja', $meja->id_meja) }}"
                                                method="POST" enctype="multipart/form-data" class="upload-form">
                                                @csrf
                                                <div class="form-group mb-2">
                                                    <select name="type" class="form-control form-control-sm" required>
                                                        <option value="">Pilih Jenis Dokumen</option>
                                                        @foreach ($types as $key => $typeInfo)
                                                        <option value="{{ $key }}">{{ $typeInfo['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <div class="custom-file">
                                                        <input type="file" name="dokumen"
                                                            class="custom-file-input"
                                                            id="file-meja-{{ $meja->id_meja }}"
                                                            accept=".pdf,.doc,.docx"
                                                            required>
                                                        <label class="custom-file-label" for="file-meja-{{ $meja->id_meja }}">
                                                            Pilih file...
                                                        </label>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                                    <i class="fas fa-upload mr-1"></i> Upload
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Document Viewer -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" role="dialog" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentViewerModalLabel">
                    <i class="fas fa-file-pdf mr-2"></i>Dokumen
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="documentViewer" src="" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
                <a href="#" class="btn btn-primary btn-download-doc" target="_blank">
                    <i class="fas fa-download mr-1"></i>Download
                </a>
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
<script src="{{ asset('js/documents.js') }}"></script>
@endpush