@extends('oee.layouts.app')

@section('title', 'Performance Analysis')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-tachometer-alt mr-2"></i>Performance Analysis
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Data Produksi</a></li>
                    <li class="breadcrumb-item active">Performance Analysis</li>
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
                        <h3>93<sup style="font-size: 20px">%</sup></h3>
                        <p>Average Performance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>480</h3>
                        <p>Total Running Time (min)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>120</h3>
                        <p>Total Downtime (min)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>15</h3>
                        <p>Stop Events</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Performance Trend
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dateRangeModal">
                                <i class="fas fa-calendar mr-1"></i> Date Range
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceTrendChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Performance Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceDistributionChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance by Machine -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>Performance by Machine
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" onclick="exportPerformanceData()">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Performance (%)</th>
                                <th>Running Time (min)</th>
                                <th>Downtime (min)</th>
                                <th>Stop Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fas fa-cog mr-2 text-primary"></i>
                                    Mesin 1
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: 92%">92%</div>
                                    </div>
                                </td>
                                <td>480</td>
                                <td>45</td>
                                <td>3</td>
                                <td><span class="badge badge-success">Running</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineDetails(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas fa-cog mr-2 text-primary"></i>
                                    Mesin 2
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-warning" style="width: 88%">88%</div>
                                    </div>
                                </td>
                                <td>450</td>
                                <td>60</td>
                                <td>5</td>
                                <td><span class="badge badge-warning">Maintenance</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineDetails(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas fa-cog mr-2 text-primary"></i>
                                    Mesin 3
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: 96%">96%</div>
                                    </div>
                                </td>
                                <td>510</td>
                                <td>30</td>
                                <td>2</td>
                                <td><span class="badge badge-success">Running</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineDetails(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Performance Factors -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Top Performance Factors
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="performance-factor">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Setup Time</span>
                                <span class="badge badge-info">25%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: 25%"></div>
                            </div>
                        </div>
                        <div class="performance-factor">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Breakdowns</span>
                                <span class="badge badge-danger">20%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="performance-factor">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Speed Loss</span>
                                <span class="badge badge-warning">15%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 15%"></div>
                            </div>
                        </div>
                        <div class="performance-factor">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Minor Stops</span>
                                <span class="badge badge-secondary">10%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-secondary" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>Performance Timeline
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 1 - High Performance</h6>
                                    <p class="timeline-text">96% performance achieved</p>
                                    <small class="text-muted">08:00 - 16:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 2 - Maintenance</h6>
                                    <p class="timeline-text">Scheduled maintenance performed</p>
                                    <small class="text-muted">16:00 - 18:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 3 - Recovery</h6>
                                    <p class="timeline-text">Performance back to 94%</p>
                                    <small class="text-muted">18:00 - 00:00</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar mr-2"></i>Select Date Range
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" class="form-control" id="startDate">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" class="form-control" id="endDate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyDateRange()">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePerformanceCharts();
});

function initializePerformanceCharts() {
    // Performance Trend Chart
    const trendCtx = document.getElementById('performanceTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Performance (%)',
                data: [92, 88, 95, 90, 93, 89, 94],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
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

    // Performance Distribution Chart
    const distributionCtx = document.getElementById('performanceDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (90-100%)', 'Good (80-89%)', 'Fair (70-79%)', 'Poor (<70%)'],
            datasets: [{
                data: [45, 30, 20, 5],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function viewMachineDetails(machineId) {
    // Implement machine details view
    console.log('Viewing machine details for ID:', machineId);
}

function exportPerformanceData() {
    // Implement export functionality
    console.log('Exporting performance data');
}

function applyDateRange() {
    // Implement date range filter
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    console.log('Applying date range:', startDate, 'to', endDate);
    $('#dateRangeModal').modal('hide');
}
</script>
@endpush

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.timeline-text {
    margin: 0 0 5px 0;
    color: #6c757d;
}

.performance-factor {
    margin-bottom: 15px;
}
</style>
@endpush
@endsection 