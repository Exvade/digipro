@extends('oee.layouts.app')

@section('title', 'Shift Analysis')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-clock mr-2"></i>Shift Analysis
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Data Produksi</a></li>
                    <li class="breadcrumb-item active">Shift Analysis</li>
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
                        <h3>3</h3>
                        <p>Active Shifts</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>24</h3>
                        <p>Hours Coverage</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>45</h3>
                        <p>Total Operators</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>8</h3>
                        <p>Hours per Shift</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Overview -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Shift Performance Overview
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dateRangeModal">
                                <i class="fas fa-calendar mr-1"></i> Date Range
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="shiftPerformanceChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Shift Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="shiftDistributionChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Details -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sun mr-2"></i>Shift 1 (08:00 - 16:00)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="shift-stats">
                            <div class="stat-item">
                                <span class="stat-label">OEE</span>
                                <span class="stat-value text-success">96%</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Production</span>
                                <span class="stat-value">450 units</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Downtime</span>
                                <span class="stat-value text-danger">30 min</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Operators</span>
                                <span class="stat-value">15</span>
                            </div>
                        </div>
                        <div class="shift-leader mt-3">
                            <strong>Shift Leader:</strong> John Smith
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title text-dark">
                            <i class="fas fa-cloud-sun mr-2"></i>Shift 2 (16:00 - 00:00)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="shift-stats">
                            <div class="stat-item">
                                <span class="stat-label">OEE</span>
                                <span class="stat-value text-warning">88%</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Production</span>
                                <span class="stat-value">400 units</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Downtime</span>
                                <span class="stat-value text-danger">60 min</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Operators</span>
                                <span class="stat-value">12</span>
                            </div>
                        </div>
                        <div class="shift-leader mt-3">
                            <strong>Shift Leader:</strong> Sarah Johnson
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title text-white">
                            <i class="fas fa-moon mr-2"></i>Shift 3 (00:00 - 08:00)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="shift-stats">
                            <div class="stat-item">
                                <span class="stat-label">OEE</span>
                                <span class="stat-value text-success">94%</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Production</span>
                                <span class="stat-value">420 units</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Downtime</span>
                                <span class="stat-value text-danger">45 min</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Operators</span>
                                <span class="stat-value">18</span>
                            </div>
                        </div>
                        <div class="shift-leader mt-3">
                            <strong>Shift Leader:</strong> Mike Davis
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Comparison Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>Shift Performance Comparison
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" onclick="exportShiftData()">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Shift</th>
                                <th>Time</th>
                                <th>OEE (%)</th>
                                <th>Production</th>
                                <th>Downtime (min)</th>
                                <th>Operators</th>
                                <th>Efficiency</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge badge-primary">Shift 1</span>
                                </td>
                                <td>08:00 - 16:00</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: 96%">96%</div>
                                    </div>
                                </td>
                                <td>450 units</td>
                                <td>30</td>
                                <td>15</td>
                                <td><span class="badge badge-success">Excellent</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewShiftDetails(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge badge-warning">Shift 2</span>
                                </td>
                                <td>16:00 - 00:00</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-warning" style="width: 88%">88%</div>
                                    </div>
                                </td>
                                <td>400 units</td>
                                <td>60</td>
                                <td>12</td>
                                <td><span class="badge badge-warning">Good</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewShiftDetails(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge badge-secondary">Shift 3</span>
                                </td>
                                <td>00:00 - 08:00</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: 94%">94%</div>
                                    </div>
                                </td>
                                <td>420 units</td>
                                <td>45</td>
                                <td>18</td>
                                <td><span class="badge badge-success">Excellent</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewShiftDetails(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift Timeline -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>Daily Shift Timeline
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="shift-timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 1 Start</h6>
                                    <p class="timeline-text">Morning shift begins</p>
                                    <small class="text-muted">08:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 2 Start</h6>
                                    <p class="timeline-text">Afternoon shift begins</p>
                                    <small class="text-muted">16:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 3 Start</h6>
                                    <p class="timeline-text">Night shift begins</p>
                                    <small class="text-muted">00:00</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>Operator Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="operator-distribution">
                            <div class="operator-group">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Shift 1 Operators</span>
                                    <span class="badge badge-primary">15</span>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 33%"></div>
                                </div>
                            </div>
                            <div class="operator-group">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Shift 2 Operators</span>
                                    <span class="badge badge-warning">12</span>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 27%"></div>
                                </div>
                            </div>
                            <div class="operator-group">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Shift 3 Operators</span>
                                    <span class="badge badge-secondary">18</span>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-secondary" style="width: 40%"></div>
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
    initializeShiftCharts();
});

function initializeShiftCharts() {
    // Shift Performance Chart
    const performanceCtx = document.getElementById('shiftPerformanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: ['Shift 1', 'Shift 2', 'Shift 3'],
            datasets: [{
                label: 'OEE (%)',
                data: [96, 88, 94],
                backgroundColor: ['#007bff', '#ffc107', '#6c757d']
            }, {
                label: 'Production (units)',
                data: [450, 400, 420],
                backgroundColor: ['#28a745', '#17a2b8', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Shift Distribution Chart
    const distributionCtx = document.getElementById('shiftDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Shift 1', 'Shift 2', 'Shift 3'],
            datasets: [{
                data: [33, 27, 40],
                backgroundColor: ['#007bff', '#ffc107', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function viewShiftDetails(shiftId) {
    // Implement shift details view
    console.log('Viewing shift details for ID:', shiftId);
}

function exportShiftData() {
    // Implement export functionality
    console.log('Exporting shift data');
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
.shift-stats {
    margin-bottom: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 500;
    color: #6c757d;
}

.stat-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.shift-leader {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    text-align: center;
}

.shift-timeline {
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

.operator-group {
    margin-bottom: 15px;
}
</style>
@endpush
@endsection 