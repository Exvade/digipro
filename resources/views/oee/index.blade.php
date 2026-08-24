@extends('oee.app')

@section('title', 'OEE Dashboard - Digital Production System')

@section('content-header')
<div class="content-header pt-3">
    <div class="container-fluid px-4">
        <div class="row mb-2 align-items-center">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <h1 class="m-0 text-center text-md-left">
                    OEE Dashboard
                </h1>
            </div>
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-center justify-content-md-end gap-2">
                    <a class="btn btn-success btn-sm btn-header-action" href="{{ route('oee.export.excel', request()->only(['period','start_date','end_date','mesin_id'])) }}">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                    <a class="btn btn-danger btn-sm btn-header-action" href="{{ route('oee.export.pdf', request()->only(['period','start_date','end_date','mesin_id'])) }}" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                    <button class="btn btn-primary btn-sm btn-header-action" data-toggle="modal" data-target="#filterModal">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Tab Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill bagian-tabs" id="bagianTabsOEE" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('bagian') ? 'active' : '' }}" id="all-tab-oee" href="#" data-bagian="">
                                <i class="fas fa-list"></i> All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('bagian') == 'inject' ? 'active' : '' }}" id="inject-tab-oee" href="#" data-bagian="inject">
                                <i class="fas fa-cog"></i> Inject
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link {{ request('bagian') == 'assy' ? 'active' : '' }}" id="assy-tab-oee" href="#" data-bagian="assy">
                                <i class="fas fa-tools"></i> Assy
                            </a>
                        </li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card oee-card">
                <div class="kpi-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="kpi-content">
                    <h6 class="kpi-title">Overall OEE</h6>
                    <h2 class="kpi-value" id="avgOEE">{{ number_format($avgOEE ?? 0, 1) }}%</h2>
                    <div class="progress">
                        <div id="prog-oee" class="progress-bar bg-primary" role="progressbar"></div>
                    </div>
                    <small class="text-muted">Target: 85%</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card availability-card">
                <div class="kpi-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kpi-content">
                    <h6 class="kpi-title">Availability</h6>
                    <h2 class="kpi-value" id="avgAvailability">{{ number_format($avgAvailability ?? 0, 1) }}%</h2>
                    <div class="progress">
                        <div id="prog-avail" class="progress-bar bg-success" role="progressbar"></div>
                    </div>
                    <small class="text-muted">Target: 90%</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card performance-card">
                <div class="kpi-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-content">
                    <h6 class="kpi-title">
                        Performance
                        <i class="fas fa-info-circle text-muted"
                            data-toggle="tooltip"
                            title="Kecepatan mesin vs standar. 100% = mesin beroperasi sesuai standar, &lt;100% = lebih lambat dari standar"
                            style="font-size: 12px;"></i>
                    </h6>
                    <h2 class="kpi-value" id="avgPerformance">{{ number_format($avgPerformance ?? 0, 1) }}%</h2>
                    <div class="progress">
                        <div id="prog-perf" class="progress-bar bg-warning" role="progressbar"></div>
                    </div>
                    <small class="text-muted">Target: 95% | Max: 100%</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card quality-card">
                <div class="kpi-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <h6 class="kpi-title">Quality</h6>
                    <h2 class="kpi-value" id="avgQuality">{{ number_format($avgQuality ?? 0, 1) }}%</h2>
                    <div class="progress">
                        <div id="prog-qual" class="progress-bar bg-info" role="progressbar"></div>
                    </div>
                    <small class="text-muted">Target: 99%</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Totals Summary --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="row">
                    <div class="col-12 col-sm-6 col-lg-2.5 mb-3" style="flex: 0 0 20%; max-width: 20%;">
                        <div class="kpi-card availability-card h-100" id="summaryPlannedCard">
                            <div class="kpi-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="kpi-content">
                                <h6 class="kpi-title">Total Planned Time</h6>
                                <h2 class="kpi-value" id="summaryPlannedValue">{{ number_format(($totalPlannedMinutes ?? 0) / 60, 1) }} jam</h2>
                                <small class="text-muted" id="summaryPlannedMinutes">{{ number_format($totalPlannedMinutes ?? 0) }} menit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2.5 mb-3" style="flex: 0 0 20%; max-width: 20%;">
                        <div class="kpi-card performance-card h-100" id="summaryRunCard">
                            <div class="kpi-icon"><i class="fas fa-play-circle"></i></div>
                            <div class="kpi-content">
                                <h6 class="kpi-title">Total Run Time</h6>
                                <h2 class="kpi-value" id="summaryRunValue">{{ number_format(($totalRunMinutes ?? 0) / 60, 1) }} jam</h2>
                                <small class="text-muted" id="summaryRunMinutes">{{ number_format($totalRunMinutes ?? 0) }} menit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2.5 mb-3" style="flex: 0 0 20%; max-width: 20%;">
                        <div class="kpi-card quality-card h-100" id="summaryDownCard">
                            <div class="kpi-icon"><i class="fas fa-pause-circle"></i></div>
                            <div class="kpi-content">
                                <h6 class="kpi-title">Total Down Time</h6>
                                <h2 class="kpi-value" id="summaryDownValue">{{ number_format(($totalDownMinutes ?? 0) / 60, 1) }} jam</h2>
                                <small class="text-muted" id="summaryDownMinutes">{{ number_format($totalDownMinutes ?? 0) }} menit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2.5 mb-3" style="flex: 0 0 20%; max-width: 20%;">
                        <div class="kpi-card success-card h-100" id="summaryOKCard">
                            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="kpi-content">
                                <h6 class="kpi-title">Total Output OK</h6>
                                <h2 class="kpi-value" id="summaryOKValue">{{ number_format($totalOutputOK ?? 0) }}</h2>
                                <small class="text-muted">Good Product</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2.5 mb-3" style="flex: 0 0 20%; max-width: 20%;">
                        <div class="kpi-card danger-card h-100" id="summaryNGCard">
                            <div class="kpi-icon"><i class="fas fa-times-circle"></i></div>
                            <div class="kpi-content">
                                <h6 class="kpi-title">Total Output NG</h6>
                                <h2 class="kpi-value" id="summaryNGValue">{{ number_format($totalOutputNG ?? 0) }}</h2>
                                <small class="text-muted">Not Good Product</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media (min-width: 1200px) {
                .col-lg-2\.5 {
                    flex: 0 0 20% !important;
                    max-width: 20% !important;
                }
            }

            @media (max-width: 1199px) and (min-width: 768px) {
                .row>[class*="col-"] {
                    flex: 0 0 33.333% !important;
                    max-width: 33.333% !important;
                }
            }

            @media (max-width: 767px) and (min-width: 576px) {
                .row>[class*="col-"] {
                    flex: 0 0 50% !important;
                    max-width: 50% !important;
                }
            }

            @media (max-width: 576px) {
                #oeeTable_wrapper .dataTables_filter {
                    text-align: center !important;
                }

                .card-tools .form-inline {
                    justify-content: center;
                    margin-top: 10px;
                }
            }
        </style>
    </div>

    {{-- OEE Charts --}}
    <div class="row mb-4">
        <div class="col-xl-8 mb-3">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><i class="fas fa-chart-area mr-2"></i>OEE Trend Analysis</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-secondary {{ ($period ?? 'month') == '7days' ? 'active' : '' }}" onclick="changePeriod('7days')">7 Days</button>
                            <button class="btn btn-outline-secondary {{ ($period ?? 'month') == '30days' ? 'active' : '' }}" onclick="changePeriod('30days')">30 Days</button>
                            <button class="btn btn-outline-secondary {{ ($period ?? 'month') == 'month' ? 'active' : '' }}" onclick="changePeriod('month')">This Month</button>
                            <button class="btn btn-outline-secondary {{ ($period ?? 'month') == 'daily' ? 'active' : '' }}" onclick="changePeriod('daily')">Today</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">
                                @if(($period ?? 'month') == 'daily')
                                <i class="fas fa-clock mr-1"></i>Showing hourly data for today
                                @elseif(($period ?? 'month') == '7days')
                                <i class="fas fa-calendar-week mr-1"></i>Showing daily data for 7 days
                                @elseif(($period ?? 'month') == '30days')
                                <i class="fas fa-calendar-alt mr-1"></i>Showing daily data for 30 days
                                @else
                                <i class="fas fa-calendar mr-1"></i>Showing daily data for this month
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="oeeChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-3">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-pie mr-2"></i>OEE Components</h5>
                </div>
                <div class="chart-body">
                    <canvas id="oeeComponentChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Machine Performance & Utilization Analysis --}}
    <div class="row mb-4">
        <div class="col-xl-12 mb-3">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="fas fa-industry mr-2"></i>Machine Performance Ranking</h5>
                </div>
                <div class="chart-body">
                    <canvas id="machineChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- NG Analysis --}}
    <div class="row mb-4">
        <div class="col-xl-12 mb-3">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="fas fa-exclamation-triangle mr-2"></i>NG Category Analysis</h5>
                </div>
                <div class="chart-body">
                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <canvas id="ngChart" height="250"></canvas>
                        </div>
                        <div class="col-lg-4">
                            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Jenis NG</th>
                                            <th class="text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $hasNgData = false;
                                        if(isset($chartData['ngLabels']) && isset($chartData['ngCategories'])) {
                                        foreach($chartData['ngCategories'] as $count) {
                                        if($count > 0) { $hasNgData = true; break; }
                                        }
                                        }
                                        @endphp

                                        @php
                                        $ngColors = [
                                        '#f8d7da', '#fa709a', '#ffc107', '#17a2b8', '#6c757d',
                                        '#667eea', '#84fab0', '#fa709a', '#30cfd0', '#f6d365',
                                        '#a1c4fd', '#c2e9fb', '#d4fc79', '#96e6a1', '#84fab0',
                                        '#ffeeee', '#ddffff', '#eeffdd', '#ffffdd', '#ffeeff'
                                        ];
                                        @endphp

                                        @if($hasNgData)
                                        @foreach($chartData['ngLabels'] as $index => $label)
                                        @if($chartData['ngCategories'][$index] > 0)
                                        @php
                                        $bgColor = $ngColors[$index % 20];
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge badge-pill" style="background-color: {{ $bgColor }}; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></span>
                                                {{ $label }}
                                            </td>
                                            <td class="text-right font-weight-bold">{{ number_format($chartData['ngCategories'][$index]) }}</td>
                                        </tr>
                                        @endif
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Tidak ada data NG untuk periode ini</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>OEE Details
                    </h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('oee.dashboard') }}" class="form-inline">
                            <div class="input-group input-group-sm">
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" placeholder="Start">
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" placeholder="End">
                                <input type="hidden" name="mesin_id" value="{{ request('mesin_id') }}">
                                <input type="hidden" name="period" value="{{ request('period', $period ?? 'month') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle" id="oeeTable" style="width:100%">
                            <thead class="text-center">
                                <tr>
                                    <th width="8%">Tanggal</th>
                                    <th width="7%">Bagian</th>
                                    <th width="12%">Mesin/Meja</th>
                                    <th width="9%">Part</th>
                                    <th width="7%">Output</th>
                                    <th width="7%">Good</th>
                                    <th width="7%">NG</th>
                                    <th width="10%">Availability</th>
                                    <th width="10%">Performance</th>
                                    <th width="10%">Quality</th>
                                    <th width="9%">OEE</th>
                                    <th width="4%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oeeData as $oee)
                                <tr data-bagian="{{ $oee->planning->bagian ?? 'inject' }}">
                                    <td>{{ $oee->tanggal->format('d-m-Y') }}</td>
                                    <td class="text-center">
                                        @if(($oee->planning->bagian ?? 'inject') === 'assy')
                                        <span class="badge badge-info">Assy</span>
                                        @else
                                        <span class="badge badge-primary">Inject</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">
                                        @if(($oee->planning->bagian ?? 'inject') === 'assy')
                                        {{ $oee->planning->meja->nama_meja ?? 'N/A' }}
                                        @else
                                        <div class="mb-1">{{ $oee->mesin->nama_mesin ?? 'N/A' }}</div>
                                        @if($oee->mesin)
                                        @if($oee->mesin->status == 'running')
                                        <span class="badge badge-success" style="font-size: 0.65rem; padding: 2px 6px;">Running</span>
                                        @elseif($oee->mesin->status == 'maintenance')
                                        <span class="badge badge-danger" style="font-size: 0.65rem; padding: 2px 6px;">Breakdown</span>
                                        @elseif($oee->mesin->status == 'stop')
                                        <span class="badge badge-warning" style="font-size: 0.65rem; padding: 2px 6px;">STAND BY {{ $oee->mesin->stop_reason ? '(' . $oee->mesin->stop_reason . ')' : '' }}</span>
                                        @elseif($oee->mesin->status == 'offline')
                                        <span class="badge badge-dark" style="font-size: 0.65rem; padding: 2px 6px;">Off</span>
                                        @endif
                                        @endif
                                        @endif
                                    </td>
                                    <td>{{ $oee->planning->part->no_parts ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ number_format($oee->actual_output) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ number_format($oee->good_output) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger"
                                            data-toggle="tooltip"
                                            data-html="true"
                                            title="
                                                @if(isset($oee->ng_details) && !empty($oee->ng_details) && (is_array($oee->ng_details) || $oee->ng_details instanceof \Countable) && count($oee->ng_details) > 0)
                                                    <div class='text-left'>
                                                        <strong>Breakdown NG:</strong><br>
                                                        @foreach($oee->ng_details as $detail)
                                                            @if($detail['quantity'] > 0)
                                                                &bull; {{ $detail['name'] }}: {{ number_format($detail['quantity']) }}<br>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class='text-left'>
                                                        <strong>Kategori NG:</strong><br>
                                                        &bull; Material: {{ number_format($oee->ng_material) }}<br>
                                                        &bull; Process: {{ number_format($oee->ng_process) }}<br>
                                                        &bull; Machine: {{ number_format($oee->ng_machine) }}<br>
                                                        &bull; Operator: {{ number_format($oee->ng_operator) }}<br>
                                                        &bull; Other: {{ number_format($oee->ng_other) }}
                                                    </div>
                                                @endif
                                              ">
                                            {{ number_format($oee->ng_output) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $oee->availability_color ?? 'secondary' }}">
                                            {{ number_format($oee->availability_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $oee->performance_color ?? 'secondary' }}">
                                            {{ number_format($oee->performance_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $oee->quality_color ?? 'secondary' }}">
                                            {{ number_format($oee->quality_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $oee->oee_color ?? 'primary' }} font-weight-bold">
                                            {{ number_format($oee->oee_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info btn-view-detail" data-id="{{ $oee->id }}" title="Detail" data-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </button>
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

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-chart-line mr-2"></i>OEE Detail
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailModalContent">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Loading data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter text-primary mr-2"></i>Filter OEE Data
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="GET" action="{{ route('oee.dashboard') }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Bagian</label>
                        <select name="bagian" id="bagianFilter" class="form-control">
                            <option value="">All Bagian</option>
                            <option value="inject" {{ request('bagian') == 'inject' ? 'selected' : '' }}>Inject</option>
                            <option value="assy" {{ request('bagian') == 'assy' ? 'selected' : '' }}>Assy</option>
                        </select>
                    </div>
                    <div class="form-group {{ request('bagian') == 'assy' ? 'd-none' : '' }}" id="mesinFilter">
                        <label class="font-weight-bold">Machine (Inject)</label>
                        <select name="mesin_id" class="form-control">
                            <option value="">All Machines</option>
                            @foreach($mesins ?? [] as $mesin)
                            <option value="{{ $mesin->id }}" {{ request('mesin_id') == $mesin->id ? 'selected' : '' }}>
                                {{ $mesin->nama_mesin }} - {{ $mesin->brand_mesin }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group {{ request('bagian') == 'inject' || !request('bagian') ? 'd-none' : '' }}" id="mejaFilter">
                        <label class="font-weight-bold">Meja (Assy)</label>
                        <select name="meja_id" class="form-control">
                            <option value="">All Meja</option>
                            @foreach($mejas ?? [] as $meja)
                            <option value="{{ $meja->id }}" {{ request('meja_id') == $meja->id ? 'selected' : '' }}>
                                {{ $meja->nama_meja }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="period" value="{{ request('period', $period ?? 'month') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Chart.js CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
{{-- Planning CSS for Tab Filter --}}
<link rel="stylesheet" href="{{ asset('css/planning.css') }}">
{{-- Custom CSS --}}
<link rel="stylesheet" href="{{ asset('css/oee-dashboard.css') }}">
@endpush

@push('scripts')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

{{-- Pass data from backend to JavaScript --}}
<div id="oee-dashboard-data"
    data-chart-data='@json($chartData ?? [])'
    data-avg-availability="{{ $avgAvailability ?? 0 }}"
    data-avg-performance="{{ $avgPerformance ?? 0 }}"
    data-avg-quality="{{ $avgQuality ?? 0 }}"
    data-period="{{ $period ?? 'month' }}"
    data-start-date="{{ $startDate ?? '' }}"
    data-end-date="{{ $endDate ?? '' }}"
    data-real-time="true"
    style="display: none;">
</div>

<script>
    const dataEl = document.getElementById('oee-dashboard-data');
    if (dataEl) {
        window.chartData = JSON.parse(dataEl.dataset.chartData);
        window.avgAvailability = parseFloat(dataEl.dataset.avgAvailability);
        window.avgPerformance = parseFloat(dataEl.dataset.avgPerformance);
        window.avgQuality = parseFloat(dataEl.dataset.avgQuality);
        window.currentPeriod = dataEl.dataset.period;
        window.startDate = dataEl.dataset.startDate;
        window.endDate = dataEl.dataset.endDate;
    }
</script>

{{-- Custom JS --}}
<script src="{{ asset('js/oee-dashboard.js') }}"></script>

{{-- Tab Filter Script --}}
<script>
    $(document).ready(function() {
        // Event delegation for view detail button
        $(document).on('click', '.btn-view-detail', function() {
            const id = $(this).data('id');
            if (typeof viewDetail === 'function') {
                viewDetail(id);
            }
        });

        // Set progress bar widths
        $('#prog-oee').css('width', window.avgOEE + '%');
        $('#prog-avail').css('width', window.avgAvailability + '%');
        $('#prog-perf').css('width', Math.min(window.avgPerformance, 100) + '%');
        $('#prog-qual').css('width', window.avgQuality + '%');

        // Tab filter untuk OEE - reload halaman dengan parameter bagian
        $('#bagianTabsOEE .nav-link').on('click', function(e) {
            e.preventDefault();
            const bagian = $(this).data('bagian');

            // Build URL dengan parameter yang ada
            const url = new URL(window.location.href);

            // Update atau hapus parameter bagian
            if (bagian && bagian !== '') {
                url.searchParams.set('bagian', bagian);
            } else {
                url.searchParams.delete('bagian');
            }

            // Hapus filter mesin/meja jika tidak sesuai dengan bagian
            if (bagian === 'assy') {
                url.searchParams.delete('mesin_id');
            } else if (bagian === 'inject') {
                url.searchParams.delete('meja_id');
            }

            // Reload halaman dengan parameter baru
            window.location.href = url.toString();
        });

        // Set bagian filter di modal sesuai dengan URL parameter saat modal dibuka
        $('#filterModal').on('show.bs.modal', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const bagianFromUrl = urlParams.get('bagian') || '';
            $('#bagianFilter').val(bagianFromUrl);
            $('#bagianFilter').trigger('change');
        });

        // Toggle filter mesin/meja berdasarkan bagian di modal
        $('#bagianFilter').on('change', function() {
            const bagian = $(this).val();

            if (bagian === 'inject') {
                $('#mesinFilter').show();
                $('#mejaFilter').hide();
                // Clear meja selection
                $('#mejaFilter select').val('');
            } else if (bagian === 'assy') {
                $('#mesinFilter').hide();
                $('#mejaFilter').show();
                // Clear mesin selection
                $('#mesinFilter select').val('');
            } else {
                // Show both when "All Bagian" selected
                $('#mesinFilter').show();
                $('#mejaFilter').show();
            }
        });

        // Trigger change on page load to set correct visibility
        $('#bagianFilter').trigger('change');
    });
</script>
@endpush