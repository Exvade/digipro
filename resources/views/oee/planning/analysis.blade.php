@extends('oee.layouts.app')

@section('title', 'Planning Analysis')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.95);
    }
    .card-header {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 20px;
    }
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    .metric-card.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    .metric-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    .metric-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .metric-label {
        font-size: 1rem;
        opacity: 0.9;
    }
    .metric-change {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    .progress-ring {
        width: 120px;
        height: 120px;
    }
    .progress-ring-circle {
        stroke: #667eea;
        stroke-width: 8;
        fill: transparent;
        stroke-linecap: round;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    .progress-ring-bg {
        stroke: #e9ecef;
        stroke-width: 8;
        fill: transparent;
    }
    .analysis-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .analysis-table thead th {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border: none;
        font-weight: 600;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="text-dark mb-1">
                        <i class="fas fa-chart-bar me-2"></i>
                        Planning Analysis
                    </h1>
                    <p class="text-muted mb-0">Analyze production planning performance and trends</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="exportAnalysis()">
                        <i class="fas fa-download me-1"></i> Export Report
                    </button>
                    <a href="{{ route('oee.planning.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Planning
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-select">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Last 3 Months</option>
                            <option>Last 6 Months</option>
                            <option>Last Year</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <select class="form-select">
                            <option value="">All Locations</option>
                            <option value="oee">OEE</option>
                            <option value="control_leader">Control Leader</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Machine/Line</label>
                        <select class="form-select">
                            <option value="">All Machines</option>
                            <option value="line1">Production Line 1</option>
                            <option value="line2">Production Line 2</option>
                            <option value="line3">Production Line 3</option>
                            <option value="machine1">Machine A</option>
                            <option value="machine2">Machine B</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Analysis Type</label>
                        <select class="form-select">
                            <option value="performance">Performance Analysis</option>
                            <option value="efficiency">Efficiency Analysis</option>
                            <option value="trends">Trend Analysis</option>
                            <option value="comparison">Comparison Analysis</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-value">85.2%</div>
                                <div class="metric-label">Planning Completion Rate</div>
                                <div class="metric-change">↑ +2.3% vs last month</div>
                            </div>
                            <div>
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-value">92.1%</div>
                                <div class="metric-label">On-Time Delivery</div>
                                <div class="metric-change">↑ +1.8% vs last month</div>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-value">78.5%</div>
                                <div class="metric-label">Resource Utilization</div>
                                <div class="metric-change">↓ -1.2% vs last month</div>
                            </div>
                            <div>
                                <i class="fas fa-cogs fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-value">12.3%</div>
                                <div class="metric-label">Planning Delays</div>
                                <div class="metric-change">↑ +0.8% vs last month</div>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Performance Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Planning Performance Trends
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Efficiency Ring -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Overall Efficiency
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center mb-3">
                                <svg class="progress-ring" viewBox="0 0 120 120">
                                    <circle class="progress-ring-bg" cx="60" cy="60" r="50"></circle>
                                    <circle class="progress-ring-circle" cx="60" cy="60" r="50" 
                                            stroke-dasharray="314" stroke-dashoffset="78.5"></circle>
                                </svg>
                            </div>
                            <h3 class="text-primary mb-1">75%</h3>
                            <p class="text-muted">Overall Planning Efficiency</p>
                            <div class="row text-center mt-3">
                                <div class="col-6">
                                    <div class="text-success">
                                        <i class="fas fa-arrow-up"></i>
                                        <small>Completed</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-warning">
                                        <i class="fas fa-arrow-right"></i>
                                        <small>In Progress</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Detailed Planning Analysis
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table analysis-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Machine/Line</th>
                                            <th>Total Planning</th>
                                            <th>Completed</th>
                                            <th>In Progress</th>
                                            <th>Delayed</th>
                                            <th>Completion Rate</th>
                                            <th>Avg Duration</th>
                                            <th>Efficiency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Production Line 1</strong><br>
                                                <small class="text-muted">OEE Location</small>
                                            </td>
                                            <td>45</td>
                                            <td><span class="badge bg-success">38</span></td>
                                            <td><span class="badge bg-warning">5</span></td>
                                            <td><span class="badge bg-danger">2</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 84.4%">84.4%</div>
                                                </div>
                                            </td>
                                            <td>6.2 hours</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-primary" style="width: 78%">78%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Production Line 2</strong><br>
                                                <small class="text-muted">OEE Location</small>
                                            </td>
                                            <td>32</td>
                                            <td><span class="badge bg-success">28</span></td>
                                            <td><span class="badge bg-warning">3</span></td>
                                            <td><span class="badge bg-danger">1</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 87.5%">87.5%</div>
                                                </div>
                                            </td>
                                            <td>5.8 hours</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-primary" style="width: 82%">82%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Machine A</strong><br>
                                                <small class="text-muted">Control Leader</small>
                                            </td>
                                            <td>28</td>
                                            <td><span class="badge bg-success">25</span></td>
                                            <td><span class="badge bg-warning">2</span></td>
                                            <td><span class="badge bg-danger">1</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 89.3%">89.3%</div>
                                                </div>
                                            </td>
                                            <td>4.5 hours</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-primary" style="width: 85%">85%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Machine B</strong><br>
                                                <small class="text-muted">Control Leader</small>
                                            </td>
                                            <td>22</td>
                                            <td><span class="badge bg-success">18</span></td>
                                            <td><span class="badge bg-warning">3</span></td>
                                            <td><span class="badge bg-danger">1</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 81.8%">81.8%</div>
                                                </div>
                                            </td>
                                            <td>7.1 hours</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-primary" style="width: 72%">72%</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights and Recommendations -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                Key Insights
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-arrow-up text-success me-2"></i>
                                    <strong>Planning completion rate improved by 2.3%</strong> compared to last month
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    <strong>Machine B shows lower efficiency (72%)</strong> - consider maintenance review
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-arrow-down text-danger me-2"></i>
                                    <strong>Resource utilization decreased by 1.2%</strong> - optimize scheduling
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Control Leader locations perform better</strong> with 89.3% completion rate
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>
                                Recommendations
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-tools text-primary me-2"></i>
                                    <strong>Schedule preventive maintenance</strong> for Machine B to improve efficiency
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <strong>Optimize shift scheduling</strong> to improve resource utilization
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-users text-info me-2"></i>
                                    <strong>Cross-train operators</strong> between OEE and Control Leader locations
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    <strong>Implement best practices</strong> from high-performing lines to others
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Completion Rate (%)',
                data: [82, 85, 83, 87, 85, 88],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Efficiency (%)',
                data: [75, 78, 76, 80, 78, 82],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Planning Performance Trends'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Export analysis function
    function exportAnalysis() {
        // Here you would typically generate and download the analysis report
        alert('Analysis report exported successfully!');
    }

    // Filter change handlers
    document.querySelectorAll('.filter-section select').forEach(select => {
        select.addEventListener('change', function() {
            // Here you would typically update the analysis based on selected criteria
            console.log('Analysis filter changed:', this.name, this.value);
        });
    });
</script>
@endpush 