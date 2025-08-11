@extends('oee.layouts.app')

@section('title', 'Production Planning')

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
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-primary {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .btn-secondary {
        background: linear-gradient(45deg, #6c757d, #495057);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .table thead th {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border: none;
        font-weight: 600;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-scheduled { background-color: #e3f2fd; color: #1976d2; }
    .status-in-progress { background-color: #fff3e0; color: #f57c00; }
    .status-completed { background-color: #e8f5e8; color: #388e3c; }
    .status-delayed { background-color: #ffebee; color: #d32f2f; }
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .section-title {
        color: #667eea;
        font-weight: 700;
        margin-bottom: 15px;
        border-bottom: 2px solid #667eea;
        padding-bottom: 8px;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    .stats-card.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    .stats-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    .stats-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="text-dark mb-1">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Production Planning
                    </h1>
                    <p class="text-muted mb-0">Manage and schedule production activities</p>
                </div>
                <div>
                    <a href="{{ route('oee.planning.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-1"></i> Add New Planning
                    </a>
                    <button class="btn btn-outline-primary me-2" onclick="exportData()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button class="btn btn-outline-secondary" onclick="printReport()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">12</h3>
                                <p class="mb-0">Total Planning</p>
                            </div>
                            <div>
                                <i class="fas fa-calendar fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">8</h3>
                                <p class="mb-0">Completed</p>
                            </div>
                            <div>
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">3</h3>
                                <p class="mb-0">In Progress</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">1</h3>
                                <p class="mb-0">Delayed</p>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planning Form Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Production Plan
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form id="planningForm" action="{{ route('oee.planning.store') }}" method="POST">
                        @csrf
                        
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Basic Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="productCode" class="form-label">
                                        <i class="fas fa-barcode me-1"></i> Product Code
                                    </label>
                                    <input type="text" class="form-control @error('productCode') is-invalid @enderror" 
                                           id="productCode" name="productCode" value="{{ old('productCode') }}" required>
                                    @error('productCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productName" class="form-label">
                                        <i class="fas fa-box me-1"></i> Product Name
                                    </label>
                                    <input type="text" class="form-control @error('productName') is-invalid @enderror" 
                                           id="productName" name="productName" value="{{ old('productName') }}" required>
                                    @error('productName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Production Details Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-chart-line me-2"></i>
                                Production Details
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="targetQuantity" class="form-label">
                                        <i class="fas fa-chart-line me-1"></i> Target Quantity
                                    </label>
                                    <input type="number" class="form-control @error('targetQuantity') is-invalid @enderror" 
                                           id="targetQuantity" name="targetQuantity" min="1" value="{{ old('targetQuantity') }}" required>
                                    @error('targetQuantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="unitOfMeasure" class="form-label">
                                        <i class="fas fa-ruler me-1"></i> Unit of Measure
                                    </label>
                                    <select class="form-select @error('unitOfMeasure') is-invalid @enderror" 
                                            id="unitOfMeasure" name="unitOfMeasure" required>
                                        <option value="">Select Unit</option>
                                        <option value="pcs" {{ old('unitOfMeasure') == 'pcs' ? 'selected' : '' }}>Pieces</option>
                                        <option value="kg" {{ old('unitOfMeasure') == 'kg' ? 'selected' : '' }}>Kilograms</option>
                                        <option value="m" {{ old('unitOfMeasure') == 'm' ? 'selected' : '' }}>Meters</option>
                                        <option value="l" {{ old('unitOfMeasure') == 'l' ? 'selected' : '' }}>Liters</option>
                                        <option value="box" {{ old('unitOfMeasure') == 'box' ? 'selected' : '' }}>Boxes</option>
                                    </select>
                                    @error('unitOfMeasure')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="priority" class="form-label">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Priority
                                    </label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-calendar me-2"></i>
                                Schedule Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="startDate" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i> Start Date & Time
                                    </label>
                                    <input type="datetime-local" class="form-control @error('startDate') is-invalid @enderror" 
                                           id="startDate" name="startDate" value="{{ old('startDate') }}" required>
                                    @error('startDate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="endDate" class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i> End Date & Time
                                    </label>
                                    <input type="datetime-local" class="form-control @error('endDate') is-invalid @enderror" 
                                           id="endDate" name="endDate" value="{{ old('endDate') }}" required>
                                    @error('endDate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Machine and Location Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-cogs me-2"></i>
                                Machine & Location
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="machineId" class="form-label">
                                        <i class="fas fa-cogs me-1"></i> Machine/Line
                                    </label>
                                    <select class="form-select @error('machineId') is-invalid @enderror" 
                                            id="machineId" name="machineId" required>
                                        <option value="">Select Machine/Line</option>
                                        <option value="line1" {{ old('machineId') == 'line1' ? 'selected' : '' }}>Production Line 1</option>
                                        <option value="line2" {{ old('machineId') == 'line2' ? 'selected' : '' }}>Production Line 2</option>
                                        <option value="line3" {{ old('machineId') == 'line3' ? 'selected' : '' }}>Production Line 3</option>
                                        <option value="machine1" {{ old('machineId') == 'machine1' ? 'selected' : '' }}>Machine A</option>
                                        <option value="machine2" {{ old('machineId') == 'machine2' ? 'selected' : '' }}>Machine B</option>
                                    </select>
                                    @error('machineId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="planningLocation" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i> Planning Location
                                    </label>
                                    <select class="form-select @error('planningLocation') is-invalid @enderror" 
                                            id="planningLocation" name="planningLocation" required>
                                        <option value="">Select Location</option>
                                        <option value="oee" {{ old('planningLocation') == 'oee' ? 'selected' : '' }}>OEE (Overall Equipment Effectiveness)</option>
                                        <option value="control_leader" {{ old('planningLocation') == 'control_leader' ? 'selected' : '' }}>Control Leader</option>
                                    </select>
                                    @error('planningLocation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Shift and Operator Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-users me-2"></i>
                                Shift & Personnel
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shift" class="form-label">
                                        <i class="fas fa-clock me-1"></i> Shift
                                    </label>
                                    <select class="form-select @error('shift') is-invalid @enderror" 
                                            id="shift" name="shift" required>
                                        <option value="">Select Shift</option>
                                        <option value="morning" {{ old('shift') == 'morning' ? 'selected' : '' }}>Morning (06:00-14:00)</option>
                                        <option value="afternoon" {{ old('shift') == 'afternoon' ? 'selected' : '' }}>Afternoon (14:00-22:00)</option>
                                        <option value="night" {{ old('shift') == 'night' ? 'selected' : '' }}>Night (22:00-06:00)</option>
                                    </select>
                                    @error('shift')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="operator" class="form-label">
                                        <i class="fas fa-user me-1"></i> Operator/Leader
                                    </label>
                                    <input type="text" class="form-control @error('operator') is-invalid @enderror" 
                                           id="operator" name="operator" value="{{ old('operator') }}" 
                                           placeholder="Enter operator or leader name">
                                    @error('operator')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-align-left me-2"></i>
                                Additional Information
                            </h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i> Description/Notes
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Enter any additional notes or special instructions...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i> Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Planning
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Planning List Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Production Plans
                    </h4>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Search plans..." style="width: 200px;">
                        <select class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
                        </select>
                        <select class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Locations</option>
                            <option value="oee">OEE</option>
                            <option value="control_leader">Control Leader</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Target Qty</th>
                                    <th>Machine/Line</th>
                                    <th>Location</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>PRD-001</strong><br>
                                            <small class="text-muted">Product A</small>
                                        </div>
                                    </td>
                                    <td>1,000 pcs</td>
                                    <td>Line 1</td>
                                    <td><span class="badge bg-primary">OEE</span></td>
                                    <td>2024-01-15 08:00</td>
                                    <td>2024-01-16 16:00</td>
                                    <td><span class="badge bg-warning">High</span></td>
                                    <td><span class="status-badge status-scheduled">Scheduled</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Start">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>PRD-002</strong><br>
                                            <small class="text-muted">Product B</small>
                                        </div>
                                    </td>
                                    <td>500 kg</td>
                                    <td>Machine A</td>
                                    <td><span class="badge bg-success">Control Leader</span></td>
                                    <td>2024-01-14 14:00</td>
                                    <td>2024-01-15 06:00</td>
                                    <td><span class="badge bg-success">Medium</span></td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Pause">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>PRD-003</strong><br>
                                            <small class="text-muted">Product C</small>
                                        </div>
                                    </td>
                                    <td>2,000 m</td>
                                    <td>Line 2</td>
                                    <td><span class="badge bg-primary">OEE</span></td>
                                    <td>2024-01-13 06:00</td>
                                    <td>2024-01-14 14:00</td>
                                    <td><span class="badge bg-info">Low</span></td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" title="View Report">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
</div>
@endsection

@push('scripts')
<script>
    // Set default dates
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        if (!document.getElementById('startDate').value) {
            document.getElementById('startDate').value = now.toISOString().slice(0, 16);
        }
        if (!document.getElementById('endDate').value) {
            document.getElementById('endDate').value = tomorrow.toISOString().slice(0, 16);
        }
    });

    // Reset form function
    function resetForm() {
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            document.getElementById('planningForm').reset();
            // Reset dates
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('startDate').value = now.toISOString().slice(0, 16);
            document.getElementById('endDate').value = tomorrow.toISOString().slice(0, 16);
        }
    }

    // Export data function
    function exportData() {
        window.location.href = '{{ route("oee.planning.export") }}';
    }

    // Print report function
    function printReport() {
        window.print();
    }

    // Auto-calculate end date based on start date and duration
    document.getElementById('startDate').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(startDate);
        endDate.setHours(endDate.getHours() + 8); // Default 8-hour shift
        document.getElementById('endDate').value = endDate.toISOString().slice(0, 16);
    });

    // Form validation
    document.getElementById('planningForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }
    });
</script>
@endpush