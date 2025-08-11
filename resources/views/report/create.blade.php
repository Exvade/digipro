@extends('layouts.app')

@section('title', 'Buat Laporan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Buat Laporan Baru
                </h3>
                <div class="card-tools">
                    <a href="{{ route('report.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="createReportForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_type">Jenis Laporan <span class="text-danger">*</span></label>
                                <select class="form-control" id="report_type" name="report_type" required>
                                    <option value="">Pilih Jenis Laporan</option>
                                    <option value="daily">Laporan Harian</option>
                                    <option value="weekly">Laporan Mingguan</option>
                                    <option value="monthly">Laporan Bulanan</option>
                                    <option value="custom">Laporan Kustom</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="approval_status">Status Approval</label>
                                <select class="form-control" id="approval_status" name="approval_status">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mesin_id">Mesin</label>
                                <select class="form-control" id="mesin_id" name="mesin_id">
                                    <option value="">Semua Mesin</option>
                                    @foreach($mesins as $mesin)
                                    <option value="{{ $mesin->id }}">{{ $mesin->nama_mesin }} - {{ $mesin->brand_mesin }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="part_id">Part</label>
                                <select class="form-control" id="part_id" name="part_id">
                                    <option value="">Semua Part</option>
                                    @foreach($parts as $part)
                                    <option value="{{ $part->id }}">{{ $part->no_parts }} - {{ $part->nama_part }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">Semua User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama }} ({{ $user->username }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shift">Shift</label>
                                <select class="form-control" id="shift" name="shift">
                                    <option value="">Semua Shift</option>
                                    <option value="pagi">Pagi</option>
                                    <option value="siang">Siang</option>
                                    <option value="malam">Malam</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="font-weight-bold">Foto Laporan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Laporan</label>
                                <div class="camera-container">
                                    <video id="camera" autoplay playsinline></video>
                                    <canvas id="canvas"></canvas>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-primary btn-sm" id="captureBtn">
                                            <i class="fas fa-camera mr-1"></i>Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="retakeBtn" style="display: none;">
                                            <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                        </button>
                                    </div>
                                </div>
                                <div id="photoPreview" class="mt-2" style="display: none;">
                                    <img id="capturedPhoto" src="" alt="Foto Laporan" class="img-thumbnail">
                                    <input type="hidden" id="photoData" name="photo_data">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keterangan Foto</label>
                                <textarea class="form-control" id="photo_description" name="photo_description" rows="4" placeholder="Masukkan keterangan untuk foto laporan..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-alt mr-1"></i>Buat Laporan
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel mr-1"></i>Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf mr-1"></i>Export PDF
                            </button>
                            <a href="{{ route('report.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/report-create.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.reportStoreUrl = @json(route('report.store'));
    window.exportExcelUrl = @json(route('report.export.excel'));
    window.exportPdfUrl = @json(route('report.export.pdf'));
</script>
<script src="{{ asset('js/report-create.js') }}"></script>
@endpush
