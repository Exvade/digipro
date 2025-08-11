@extends('layouts.app')

@section('title', 'Test Export')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-download mr-2"></i>
                    Test Export Functionality
                </h3>
            </div>
            <div class="card-body">
                <form id="testExportForm">
                    @csrf
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
                                <label for="report_type">Jenis Laporan</label>
                                <select class="form-control" id="report_type" name="report_type">
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

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" onclick="testExport('excel')">
                                    <i class="fas fa-file-excel mr-1"></i>Test Export Excel
                                </button>
                                <button type="button" class="btn btn-danger" onclick="testExport('pdf')">
                                    <i class="fas fa-file-pdf mr-1"></i>Test Export PDF
                                </button>
                                <a href="{{ route('report.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/report-test-export.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.exportExcelUrl = @json(route('report.export.excel'));
    window.exportPdfUrl = @json(route('report.export.pdf'));
</script>
<script src="{{ asset('js/report-test-export.js') }}"></script>
@endpush 