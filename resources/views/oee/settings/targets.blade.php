@extends('oee.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pengaturan Target OEE</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Target OEE</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTargetModal">
                    <i class="fas fa-plus"></i> Tambah Target
                </button>
                <button type="button" class="btn btn-success" onclick="importTargets()">
                    <i class="fas fa-upload"></i> Import Excel
                </button>
                <button type="button" class="btn btn-info" onclick="exportTargets()">
                    <i class="fas fa-download"></i> Export Excel
                </button>
            </div>
        </div>

        <!-- Target Cards -->
        <div class="row">
            @foreach($targets as $target)
            <div class="col-lg-4 col-md-6">
                <div class="card target-card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $target->mesin->nama_mesin }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-{{ $target->status_color }}">
                                {{ $target->is_effective ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="target-metric">
                                    <h4 class="text-info">{{ $target->target_oee }}%</h4>
                                    <small>Target OEE</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="target-metric">
                                    <h4 class="text-primary">{{ $target->target_cycle_time }}s</h4>
                                    <small>Cycle Time</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="target-detail">
                                    <span class="target-value text-success">{{ $target->target_availability }}%</span>
                                    <small class="d-block">Availability</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="target-detail">
                                    <span class="target-value text-warning">{{ $target->target_performance }}%</span>
                                    <small class="d-block">Performance</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="target-detail">
                                    <span class="target-value text-danger">{{ $target->target_quality }}%</span>
                                    <small class="d-block">Quality</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <small><strong>Max Downtime:</strong> {{ $target->max_downtime_per_day }} min/day</small>
                            </div>
                            <div class="col-6">
                                <small><strong>Max Stops:</strong> {{ $target->max_stops_per_day }}/day</small>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <small class="text-muted">
                                <strong>Period:</strong> {{ ucfirst($target->period_type) }}<br>
                                <strong>Effective:</strong> {{ $target->effective_date->format('d/m/Y') }}
                                @if($target->expiry_date)
                                <br><strong>Expires:</strong> {{ $target->expiry_date->format('d/m/Y') }}
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn btn-info" onclick="viewTargetDetails({{ $target->id }})">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button type="button" class="btn btn-warning" onclick="editTarget({{ $target->id }})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteTarget({{ $target->id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="row">
            <div class="col-12">
                {{ $targets->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Target Modal -->
<div class="modal fade" id="addTargetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Target OEE</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addTargetForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mesin</label>
                                <select class="form-control" name="mesin_id" required>
                                    <option value="">Pilih Mesin</option>
                                    @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}">{{ $machine->nama_mesin }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Period Type</label>
                                <select class="form-control" name="period_type" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Target OEE (%)</label>
                                <input type="number" class="form-control" name="target_oee" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Target Cycle Time (detik)</label>
                                <input type="number" class="form-control" name="target_cycle_time" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Target Availability (%)</label>
                                <input type="number" class="form-control" name="target_availability" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Target Performance (%)</label>
                                <input type="number" class="form-control" name="target_performance" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Target Quality (%)</label>
                                <input type="number" class="form-control" name="target_quality" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Downtime per Day (menit)</label>
                                <input type="number" class="form-control" name="max_downtime_per_day" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Stops per Day</label>
                                <input type="number" class="form-control" name="max_stops_per_day" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Effective Date</label>
                                <input type="date" class="form-control" name="effective_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expiry Date (Optional)</label>
                                <input type="date" class="form-control" name="expiry_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewTargetDetails(targetId) {
    window.location.href = `/oee/settings/targets/${targetId}/details`;
}

function editTarget(targetId) {
    window.location.href = `/oee/settings/targets/${targetId}/edit`;
}

function deleteTarget(targetId) {
    if (confirm('Apakah Anda yakin ingin menghapus target ini?')) {
        $.ajax({
            url: `/oee/settings/targets/${targetId}`,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus target');
                }
            },
            error: function() {
                alert('Terjadi kesalahan');
            }
        });
    }
}

function importTargets() {
    // Create file input
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.xlsx,.xls';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: '/oee/settings/targets/import',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Gagal import data');
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan');
                }
            });
        }
    };
    input.click();
}

function exportTargets() {
    window.location.href = '/oee/settings/targets/export';
}

// Add Target Form Handler
$('#addTargetForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '/oee/settings/targets',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                $('#addTargetModal').modal('hide');
                location.reload();
            } else {
                alert('Gagal menambahkan target');
            }
        },
        error: function() {
            alert('Terjadi kesalahan');
        }
    });
});

// Set default effective date to today
$(document).ready(function() {
    $('input[name="effective_date"]').val('{{ date("Y-m-d") }}');
});
</script>

<style>
.target-card {
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.target-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.target-metric h4 {
    margin: 0;
    font-weight: bold;
}

.target-detail .target-value {
    font-size: 1.1em;
    font-weight: bold;
}

.card-tools .badge {
    font-size: 0.8em;
}
</style>
@endpush
@endsection 