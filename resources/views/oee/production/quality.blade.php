@extends('oee.layouts.app')

@section('title', 'Quality Analysis')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-star mr-2"></i>Quality Analysis
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Data Produksi</a></li>
                    <li class="breadcrumb-item active">Quality Analysis</li>
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
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>99<sup style="font-size: 20px">%</sup></h3>
                        <p>Quality Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>1,250</h3>
                        <p>Total Produced</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>12</h3>
                        <p>Defects</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>8</h3>
                        <p>Rework Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-redo"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>Quality Trend
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dateRangeModal">
                                <i class="fas fa-calendar mr-1"></i> Date Range
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="qualityTrendChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Defect Types
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="defectTypesChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality by Machine -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>Quality by Machine
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" onclick="exportQualityData()">
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
                                <th>Quality Rate (%)</th>
                                <th>Produced</th>
                                <th>Good</th>
                                <th>Defects</th>
                                <th>Rework</th>
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
                                        <div class="progress-bar bg-success" style="width: 98%">98%</div>
                                    </div>
                                </td>
                                <td>450</td>
                                <td>441</td>
                                <td>6</td>
                                <td>3</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineQuality(1)">
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
                                        <div class="progress-bar bg-warning" style="width: 95%">95%</div>
                                    </div>
                                </td>
                                <td>400</td>
                                <td>380</td>
                                <td>15</td>
                                <td>5</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineQuality(2)">
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
                                        <div class="progress-bar bg-success" style="width: 99%">99%</div>
                                    </div>
                                </td>
                                <td>400</td>
                                <td>396</td>
                                <td>2</td>
                                <td>2</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewMachineQuality(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Defect Analysis -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Top Defect Causes
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="defect-cause">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Material Issues</span>
                                <span class="badge badge-danger">40%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: 40%"></div>
                            </div>
                        </div>
                        <div class="defect-cause">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Operator Error</span>
                                <span class="badge badge-warning">30%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 30%"></div>
                            </div>
                        </div>
                        <div class="defect-cause">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Machine Calibration</span>
                                <span class="badge badge-info">20%</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="defect-cause">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Environmental</span>
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
                            <i class="fas fa-clock mr-2"></i>Quality Timeline
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 1 - Excellent Quality</h6>
                                    <p class="timeline-text">99.2% quality rate achieved</p>
                                    <small class="text-muted">08:00 - 16:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 2 - Minor Issues</h6>
                                    <p class="timeline-text">Material quality issues detected</p>
                                    <small class="text-muted">16:00 - 18:00</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Shift 3 - Recovery</h6>
                                    <p class="timeline-text">Quality back to 98.5%</p>
                                    <small class="text-muted">18:00 - 00:00</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality Metrics -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator mr-2"></i>Quality Metrics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="metric-item">
                            <div class="metric-label">First Pass Yield (FPY)</div>
                            <div class="metric-value">97.5%</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Defects Per Million (DPMO)</div>
                            <div class="metric-value">12,500</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Cost of Poor Quality</div>
                            <div class="metric-value">$2,450</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Customer Complaints</div>
                            <div class="metric-value">2</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Recent Quality Events
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quality-events">
                            <div class="event-item">
                                <div class="event-icon bg-danger">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="event-content">
                                    <h6>Material Defect Detected</h6>
                                    <p>Batch #12345 showed surface irregularities</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-icon bg-warning">
                                    <i class="fas fa-exclamation"></i>
                                </div>
                                <div class="event-content">
                                    <h6>Operator Training Required</h6>
                                    <p>Operator John needs refresher training</p>
                                    <small class="text-muted">4 hours ago</small>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="event-content">
                                    <h6>Quality Target Achieved</h6>
                                    <p>Weekly quality target of 99% exceeded</p>
                                    <small class="text-muted">1 day ago</small>
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
    initializeQualityCharts();
});

function initializeQualityCharts() {
    // Quality Trend Chart
    const trendCtx = document.getElementById('qualityTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Quality Rate (%)',
                data: [99, 98, 99.5, 97, 99, 98.5, 99.2],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
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

    // Defect Types Chart
    const defectCtx = document.getElementById('defectTypesChart').getContext('2d');
    new Chart(defectCtx, {
        type: 'doughnut',
        data: {
            labels: ['Material Issues', 'Operator Error', 'Machine Calibration', 'Environmental'],
            datasets: [{
                data: [40, 30, 20, 10],
                backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function viewMachineQuality(machineId) {
    // Implement machine quality details view
    console.log('Viewing machine quality for ID:', machineId);
}

function exportQualityData() {
    // Implement export functionality
    console.log('Exporting quality data');
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

.defect-cause {
    margin-bottom: 15px;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f8f9fa;
}

.metric-item:last-child {
    border-bottom: none;
}

.metric-label {
    font-weight: 500;
    color: #6c757d;
}

.metric-value {
    font-weight: 700;
    font-size: 1.1rem;
    color: #007bff;
}

.quality-events {
    max-height: 400px;
    overflow-y: auto;
}

.event-item {
    display: flex;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid #f8f9fa;
}

.event-item:last-child {
    border-bottom: none;
}

.event-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.event-content h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.event-content p {
    margin: 0 0 5px 0;
    color: #6c757d;
}
</style>
@endpush
@endsection 