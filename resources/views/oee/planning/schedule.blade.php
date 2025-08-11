@extends('oee.layouts.app')

@section('title', 'Production Schedule')

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
    .schedule-timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding: 15px 20px;
        margin-bottom: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 4px solid #667eea;
    }
    .timeline-item.oee {
        border-left-color: #667eea;
    }
    .timeline-item.control-leader {
        border-left-color: #28a745;
    }
    .timeline-item.completed {
        border-left-color: #28a745;
        opacity: 0.7;
    }
    .timeline-item.in-progress {
        border-left-color: #ffc107;
    }
    .timeline-item.delayed {
        border-left-color: #dc3545;
    }
    .timeline-time {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
    }
    .timeline-title {
        font-weight: 700;
        color: #495057;
        margin: 5px 0;
    }
    .timeline-details {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-scheduled { background-color: #e3f2fd; color: #1976d2; }
    .status-in-progress { background-color: #fff3e0; color: #f57c00; }
    .status-completed { background-color: #e8f5e8; color: #388e3c; }
    .status-delayed { background-color: #ffebee; color: #d32f2f; }
    .calendar-view {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .calendar-day {
        aspect-ratio: 1;
        border: 1px solid #e9ecef;
        padding: 5px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .calendar-day:hover {
        background-color: #f8f9fa;
    }
    .calendar-day.today {
        background-color: #667eea;
        color: white;
        font-weight: bold;
    }
    .calendar-day.has-planning {
        background-color: #e3f2fd;
        border-color: #667eea;
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
                        <i class="fas fa-calendar-alt me-2"></i>
                        Production Schedule
                    </h1>
                    <p class="text-muted mb-0">View and manage production schedules</p>
                </div>
                <div>
                    <a href="{{ route('oee.planning.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-1"></i> Add Planning
                    </a>
                    <a href="{{ route('oee.planning.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> Planning List
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-select">
                            <option>Today</option>
                            <option>This Week</option>
                            <option>This Month</option>
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
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
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
                </div>
            </div>

            <div class="row">
                <!-- Calendar View -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Calendar View
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="calendar-view">
                                <div class="calendar-header">
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <h6 class="mb-0">January 2024</h6>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="calendar-grid">
                                    <!-- Calendar days would be generated here -->
                                    <div class="calendar-day">1</div>
                                    <div class="calendar-day">2</div>
                                    <div class="calendar-day has-planning">3</div>
                                    <div class="calendar-day">4</div>
                                    <div class="calendar-day has-planning">5</div>
                                    <div class="calendar-day">6</div>
                                    <div class="calendar-day">7</div>
                                    <div class="calendar-day">8</div>
                                    <div class="calendar-day has-planning">9</div>
                                    <div class="calendar-day">10</div>
                                    <div class="calendar-day">11</div>
                                    <div class="calendar-day has-planning">12</div>
                                    <div class="calendar-day">13</div>
                                    <div class="calendar-day">14</div>
                                    <div class="calendar-day today">15</div>
                                    <div class="calendar-day">16</div>
                                    <div class="calendar-day">17</div>
                                    <div class="calendar-day">18</div>
                                    <div class="calendar-day">19</div>
                                    <div class="calendar-day">20</div>
                                    <div class="calendar-day">21</div>
                                    <div class="calendar-day">22</div>
                                    <div class="calendar-day">23</div>
                                    <div class="calendar-day">24</div>
                                    <div class="calendar-day">25</div>
                                    <div class="calendar-day">26</div>
                                    <div class="calendar-day">27</div>
                                    <div class="calendar-day">28</div>
                                    <div class="calendar-day">29</div>
                                    <div class="calendar-day">30</div>
                                    <div class="calendar-day">31</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline View -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Today's Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="schedule-timeline">
                                <!-- Morning Shift -->
                                <div class="timeline-item oee in-progress">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="timeline-time">06:00 - 14:00</div>
                                            <div class="timeline-title">Product A Production</div>
                                            <div class="timeline-details">
                                                <strong>Line 1</strong> • Target: 1,000 pcs • OEE Location
                                            </div>
                                        </div>
                                        <div>
                                            <span class="status-badge status-in-progress">In Progress</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item control-leader scheduled">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="timeline-time">08:00 - 16:00</div>
                                            <div class="timeline-title">Product B Setup</div>
                                            <div class="timeline-details">
                                                <strong>Machine A</strong> • Target: 500 kg • Control Leader
                                            </div>
                                        </div>
                                        <div>
                                            <span class="status-badge status-scheduled">Scheduled</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Afternoon Shift -->
                                <div class="timeline-item oee scheduled">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="timeline-time">14:00 - 22:00</div>
                                            <div class="timeline-title">Product C Production</div>
                                            <div class="timeline-details">
                                                <strong>Line 2</strong> • Target: 2,000 m • OEE Location
                                            </div>
                                        </div>
                                        <div>
                                            <span class="status-badge status-scheduled">Scheduled</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item control-leader scheduled">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="timeline-time">16:00 - 00:00</div>
                                            <div class="timeline-title">Product D Maintenance</div>
                                            <div class="timeline-details">
                                                <strong>Machine B</strong> • Preventive Maintenance • Control Leader
                                            </div>
                                        </div>
                                        <div>
                                            <span class="status-badge status-scheduled">Scheduled</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Night Shift -->
                                <div class="timeline-item oee delayed">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="timeline-time">22:00 - 06:00</div>
                                            <div class="timeline-title">Product E Production</div>
                                            <div class="timeline-details">
                                                <strong>Line 3</strong> • Target: 1,500 pcs • OEE Location
                                            </div>
                                        </div>
                                        <div>
                                            <span class="status-badge status-delayed">Delayed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">12</h4>
                                    <p class="mb-0">Total Planning</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">8</h4>
                                    <p class="mb-0">Completed</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">3</h4>
                                    <p class="mb-0">In Progress</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">1</h4>
                                    <p class="mb-0">Delayed</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Calendar day click handler
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.addEventListener('click', function() {
            // Remove active class from all days
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('active'));
            // Add active class to clicked day
            this.classList.add('active');
            
            // Here you would typically load the schedule for the selected date
            console.log('Selected date:', this.textContent);
        });
    });

    // Filter change handlers
    document.querySelectorAll('.filter-section select').forEach(select => {
        select.addEventListener('change', function() {
            // Here you would typically filter the schedule based on selected criteria
            console.log('Filter changed:', this.name, this.value);
        });
    });
</script>
@endpush 