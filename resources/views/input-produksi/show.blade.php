@extends('input-produksi.app')

@section('title', 'Detail Input Produksi')

@push('styles')
<style>
    .detail-card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: none;
        background-color: #fff;
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        color: #212529;
        font-size: 1rem;
        font-weight: 500;
    }

    .stat-card {
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        color: white;
        transition: transform 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-card.stat-target {
        background: linear-gradient(135deg, #0d47a1 0%, #42a5f5 100%);
    }

    .stat-card.stat-ok {
        background: linear-gradient(135deg, #1b5e20 0%, #66bb6a 100%);
    }

    .stat-card.stat-ng {
        background: linear-gradient(135deg, #b71c1c 0%, #ef5350 100%);
    }

    .stat-card.stat-total {
        background: linear-gradient(135deg, #006064 0%, #26c6da 100%);
    }

    .stat-card.stat-gap {
        background: linear-gradient(135deg, #4a148c 0%, #ab47bc 100%);
    }

    .stat-card.stat-efficiency {
        background: #212529;
        color: #ffeb3b;
    }

    .stat-number {
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .ng-item {
        background-color: #fff;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }

    .ng-item:hover {
        border-color: #dc3545;
        background-color: #fff5f5;
    }

    .ng-count {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
    }

    .page-header-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border-radius: 50%;
        margin-right: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="page-header-icon">
                        <i class="fas fa-eye fa-lg"></i>
                    </div>
                    <div>
                        <h1 class="h4 mb-1 font-weight-bold">Detail Input Produksi</h1>
                        <p class="text-muted mb-0">ID Transaksi: #{{ $input->id }}</p>
                    </div>
                </div>
                <a href="{{ route('input-produksi.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik Produksi --}}
    <div class="card detail-card mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-chart-line mr-2 text-primary"></i>Statistik Produksi
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-card stat-target">
                        <div class="stat-label">Target</div>
                        <div class="stat-number">{{ number_format($target) }}</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-card stat-ok">
                        <div class="stat-label">Good (OK)</div>
                        <div class="stat-number">{{ number_format($input->ok_count ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-card stat-ng">
                        <div class="stat-label">Defect (NG)</div>
                        <div class="stat-number">{{ number_format($input->ng_count ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-total">
                        <div class="stat-label">Total Produksi</div>
                        <div class="stat-number">{{ number_format($total) }}</div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="stat-card stat-gap" style="min-height: 100px; flex-direction: row; justify-content: space-between; padding-left: 2rem; padding-right: 2rem;">
                        <div class="text-left">
                            <div class="stat-label">Gap</div>
                            <div class="small opacity-75">Target vs Total</div>
                        </div>
                        <div class="stat-number">{{ number_format($gap) }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card stat-efficiency" style="min-height: 100px; flex-direction: row; justify-content: space-between; padding-left: 2rem; padding-right: 2rem;">
                        <div class="text-left">
                            <div class="stat-label text-white">Efficiency</div>
                            <div class="small text-white-50">Performance</div>
                        </div>
                        <div class="stat-number">{{ $efficiency }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail NG --}}
    @if (($input->ng_count ?? 0) > 0)
    <div class="card detail-card mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Detail NG per Kategori
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(['material' => 'Material', 'process' => 'Process', 'machine' => 'Machine', 'operator' => 'Operator', 'other' => 'Other'] as $key => $label)
                @php $val = $input->{'ng_'.$key} ?? 0; @endphp
                @if($val > 0)
                <div class="col-md-2 mb-3">
                    <div class="ng-item h-100">
                        <div class="info-label mb-2">NG {{ $label }}</div>
                        <div class="ng-count">{{ number_format($val) }}</div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Detailed Info Grid --}}
    <div class="row">
        {{-- Informasi Dasar --}}
        <div class="col-lg-4 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>Informasi Dasar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Tanggal Input</div>
                        <div class="info-value">{{ $input->created_at ? $input->created_at->format('d/m/Y H:i:s') : '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Status</div>
                        <div class="mt-1">
                            @if ($input->status === 'approved')
                            <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i>Approved</span>
                            @elseif ($input->status === 'rejected')
                            <span class="badge badge-danger px-3 py-2"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                            @elseif ($input->status === 'pending')
                            <span class="badge badge-warning px-3 py-2"><i class="fas fa-clock mr-1"></i>Pending</span>
                            @else
                            <span class="badge badge-secondary px-3 py-2"><i class="fas fa-spinner mr-1"></i>In Progress</span>
                            @endif
                        </div>
                    </div>
                    @if ($input->submitted_at)
                    <div class="mb-3">
                        <div class="info-label">Tanggal Submit</div>
                        <div class="info-value">{{ $input->submitted_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    @endif
                    @if ($input->approved_at)
                    <div class="mb-3">
                        <div class="info-label">Tanggal Approval</div>
                        <div class="info-value">{{ $input->approved_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    @endif
                    @if ($input->rejected_at)
                    <div class="mb-3">
                        <div class="info-label">Tanggal Rejection</div>
                        <div class="info-value">{{ $input->rejected_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Planning Info --}}
        <div class="col-lg-4 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-clipboard-list mr-2 text-info"></i>Planning Produksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Part Code</div>
                        <div class="info-value font-weight-bold">{{ $input->planning?->part?->no_parts ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Part Name</div>
                        <div class="info-value">{{ $input->planning?->part?->nama_part ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Machine</div>
                        <div class="info-value">{{ $input->planning?->mesin?->nama_mesin ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Mold</div>
                        <div class="info-value">{{ $input->planning?->moldshop?->nama_mold ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Customer</div>
                        <div class="info-value">{{ $input->planning?->moldshop?->customer ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operator Info --}}
        <div class="col-lg-4 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-user mr-2 text-success"></i>Operator & Approval
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Operator</div>
                        <div class="info-value font-weight-bold">
                            {{ $input->ploting?->user?->nama ?? ($input->ploting?->operator?->nama ?? 'N/A') }}
                        </div>
                    </div>
                    @if ($input->user)
                    <div class="mb-3">
                        <div class="info-label">
                            @if ($input->status === 'approved') Approved By
                            @elseif ($input->status === 'rejected') Rejected By
                            @else User
                            @endif
                        </div>
                        <div class="info-value">{{ $input->user->nama ?? 'N/A' }}</div>
                    </div>
                    @endif

                    @if ($input->status === 'rejected' && $input->ng_description)
                    <div class="mt-4 p-3 bg-light border border-danger rounded">
                        <div class="info-label text-danger mb-1">Alasan Rejection</div>
                        <p class="mb-0 text-danger small">{{ $input->ng_description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection