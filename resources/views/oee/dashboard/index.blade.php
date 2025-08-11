@extends('oee.layouts.app')

@section('title', 'Dashboard OEE')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard OEE
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard OEE</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="oee-year">{{ $summary['oee_year'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                        <p>OEE Tahun Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 id="availability">{{ $summary['availability'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Availability</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="performance">{{ $summary['performance'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Performance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="quality">{{ $summary['quality'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Quality</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Status Cards -->
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="active-machines">0</h3>
                        <p>Mesin Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="maintenance-machines">0</h3>
                        <p>Mesin Maintenance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="downtime-events">0</h3>
                        <p>Downtime Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="avg-oee">0%</h3>
                        <p>Rata-rata OEE</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">OEE NG Product</span>
                        <span class="info-box-number">{{ $summary['ng_product'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">OEE NG Process</span>
                        <span class="info-box-number">{{ $summary['ng_process'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Down Time</span>
                        <span class="info-box-number">{{ $summary['total_downtime'] ?? 0 }} menit</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-play-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Running Time</span>
                        <span class="info-box-number">{{ $summary['running_time'] ?? 0 }} menit</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">OEE Trend Real-time</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="oeeTrendChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Downtime Distribution</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="downtimeChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Availability Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="availabilityChart" style="height: 200px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Performance Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" style="height: 200px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quality Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="qualityChart" style="height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Downtime Events Terbaru</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mesin</th>
                                        <th>Kategori</th>
                                        <th>Durasi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-downtime">
                                    @if($recentDowntime && $recentDowntime->count() > 0)
                                        @foreach($recentDowntime as $event)
                                        <tr>
                                            <td>{{ $event->mesin->nama_mesin ?? 'N/A' }}</td>
                                            <td>{{ $event->category->name ?? 'N/A' }}</td>
                                            <td>{{ $event->duration ?? 0 }} menit</td>
                                            <td><span class="badge badge-{{ $event->status === 'active' ? 'danger' : 'success' }}">{{ $event->status }}</span></td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="4" class="text-center text-muted">Tidak ada downtime events hari ini</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quality Events Terbaru</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mesin</th>
                                        <th>Defect Type</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-quality">
                                    @if($recentQuality && $recentQuality->count() > 0)
                                        @foreach($recentQuality as $event)
                                        <tr>
                                            <td>{{ $event->mesin->nama_mesin ?? 'N/A' }}</td>
                                            <td>{{ $event->defect_type ?? 'N/A' }}</td>
                                            <td>{{ $event->quantity ?? 0 }}</td>
                                            <td><span class="badge badge-{{ $event->status === 'open' ? 'danger' : 'success' }}">{{ $event->status }}</span></td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="4" class="text-center text-muted">Tidak ada quality events hari ini</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Machine List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status Mesin Hari Ini</h3>
                    </div>
                    <div class="card-body">
                        @if($machines && $machines->count() > 0)
                            <div class="row">
                                @foreach($machines as $machine)
                                <div class="col-md-3 mb-3">
                                    <div class="card machine-status-card">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">{{ $machine->nama_mesin }}</h5>
                                            <p class="card-text">
                                                <span class="badge badge-{{ $machine->status_color }}">
                                                    {{ ucfirst($machine->status) }}
                                                </span>
                                            </p>
                                            @if($machine->oeeData->count() > 0)
                                                @php $latestOee = $machine->oeeData->first(); @endphp
                                                <div class="oee-info">
                                                    <small class="text-muted">OEE: {{ $latestOee->oee ?? 0 }}%</small><br>
                                                    <small class="text-muted">A: {{ $latestOee->availability ?? 0 }}% | P: {{ $latestOee->performance ?? 0 }}% | Q: {{ $latestOee->quality ?? 0 }}%</small>
                                                </div>
                                            @else
                                                <small class="text-muted">Belum ada data OEE</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center">Tidak ada mesin terdaftar</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Status</h3>
                        <div class="card-tools">
                            <span class="badge badge-success" id="system-status">Online</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Data Collection</span>
                                        <span class="info-box-number" id="data-collection-status">Active</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Analytics</span>
                                        <span class="info-box-number" id="analytics-status">Running</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-bell"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Alerts</span>
                                        <span class="info-box-number" id="alerts-count">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Last Update</span>
                                        <span class="info-box-number" id="last-update">Just now</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dashboard functionality with real-time updates
let oeeTrendChart, downtimeChart, availabilityChart, performanceChart, qualityChart;

$(document).ready(function() {
    initializeCharts();
    loadRealTimeData();
    
    // Refresh data every 30 seconds
    setInterval(loadRealTimeData, 30000);
    
    // Update last update time
    setInterval(updateLastUpdate, 1000);
});

function initializeCharts() {
    // OEE Trend Chart
    const oeeCtx = document.getElementById('oeeTrendChart').getContext('2d');
    oeeTrendChart = new Chart(oeeCtx, {
        type: 'line',
        data: {
            labels: ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            datasets: [{
                label: 'OEE (%)',
                data: [85, 87, 82, 89, 91, 88, 86],
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Downtime Distribution Chart
    const downtimeCtx = document.getElementById('downtimeChart').getContext('2d');
    downtimeChart = new Chart(downtimeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mechanical', 'Electrical', 'Setup', 'Material'],
            datasets: [{
                data: [30, 25, 20, 25],
                backgroundColor: [
                    '#dc3545',
                    '#fd7e14',
                    '#ffc107',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Availability Chart
    const availabilityCtx = document.getElementById('availabilityChart').getContext('2d');
    availabilityChart = new Chart(availabilityCtx, {
        type: 'line',
        data: {
            labels: ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            datasets: [{
                label: 'Availability (%)',
                data: [90, 92, 88, 95, 93, 91, 89],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            datasets: [{
                label: 'Performance (%)',
                data: [88, 90, 85, 92, 94, 91, 89],
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Quality Chart
    const qualityCtx = document.getElementById('qualityChart').getContext('2d');
    qualityChart = new Chart(qualityCtx, {
        type: 'line',
        data: {
            labels: ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
            datasets: [{
                label: 'Quality (%)',
                data: [95, 96, 94, 97, 98, 96, 95],
                borderColor: 'rgba(220, 53, 69, 1)',
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function loadRealTimeData() {
    // Set up CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load machine status
    $.ajax({
        url: '/oee/dashboard/machine-status',
        type: 'GET',
        dataType: 'json',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(data) {
            console.log('Machine status data:', data);
            updateMachineStatus(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading machine status:', error);
            console.log('Response:', xhr.responseText);
            console.log('Status:', xhr.status);
            // Fallback to static data if API fails
            console.log('Real-time API not available, using static data');
        }
    });

    // Load recent events
    $.ajax({
        url: '/oee/dashboard/recent-events',
        type: 'GET',
        dataType: 'json',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(data) {
            console.log('Recent events data:', data);
            updateRecentEvents(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading recent events:', error);
            // Keep existing static data if API fails
            console.log('Recent events API not available');
        }
    });

    // Load chart data
    $.ajax({
        url: '/oee/dashboard/chart-data',
        type: 'GET',
        dataType: 'json',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(data) {
            console.log('Chart data:', data);
            updateCharts(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading chart data:', error);
            // Keep existing chart data if API fails
            console.log('Chart data API not available');
        }
    });
}

function updateMachineStatus(data) {
    $('#active-machines').text(data.active_count);
    $('#maintenance-machines').text(data.maintenance_count);
    $('#downtime-events').text(data.downtime_count);
    $('#avg-oee').text(data.avg_oee + '%');
}

function updateRecentEvents(data) {
    // Update downtime events
    let downtimeHtml = '';
    data.downtime_events.forEach(function(event) {
        downtimeHtml += `
            <tr>
                <td>${event.mesin.nama_mesin}</td>
                <td>${event.category.name}</td>
                <td>${event.duration_minutes} min</td>
                <td><span class="badge badge-${event.status === 'active' ? 'danger' : 'success'}">${event.status}</span></td>
            </tr>
        `;
    });
    $('#recent-downtime').html(downtimeHtml);

    // Update quality events
    let qualityHtml = '';
    data.quality_events.forEach(function(event) {
        qualityHtml += `
            <tr>
                <td>${event.mesin.nama_mesin}</td>
                <td>${event.defect_type}</td>
                <td>${event.quantity_defective}</td>
                <td><span class="badge badge-${event.status === 'open' ? 'danger' : 'success'}">${event.status}</span></td>
            </tr>
        `;
    });
    $('#recent-quality').html(qualityHtml);
}

function updateCharts(data) {
    // Update OEE trend chart
    if (data.oee_trend) {
        oeeTrendChart.data.labels = data.oee_trend.labels;
        oeeTrendChart.data.datasets[0].data = data.oee_trend.data;
        oeeTrendChart.update();
    }

    // Update downtime chart
    if (data.downtime_distribution) {
        downtimeChart.data.labels = data.downtime_distribution.labels;
        downtimeChart.data.datasets[0].data = data.downtime_distribution.data;
        downtimeChart.update();
    }

    // Update availability chart
    if (data.availability_trend) {
        availabilityChart.data.labels = data.availability_trend.labels;
        availabilityChart.data.datasets[0].data = data.availability_trend.data;
        availabilityChart.update();
    }

    // Update performance chart
    if (data.performance_trend) {
        performanceChart.data.labels = data.performance_trend.labels;
        performanceChart.data.datasets[0].data = data.performance_trend.data;
        performanceChart.update();
    }

    // Update quality chart
    if (data.quality_trend) {
        qualityChart.data.labels = data.quality_trend.labels;
        qualityChart.data.datasets[0].data = data.quality_trend.data;
        qualityChart.update();
    }
}

function updateLastUpdate() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    $('#last-update').text(timeString);
}
</script>

<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
}

.info-box-icon {
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
    color: #fff;
}

.info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box-text {
    display: block;
    font-size: 1rem;
    font-weight: 300;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: 700;
}

.machine-status-card {
    transition: transform 0.2s;
}

.machine-status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
