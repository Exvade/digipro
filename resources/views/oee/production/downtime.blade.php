@extends('oee.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Analisis Downtime</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Analisis Downtime</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form id="filterForm" class="row">
                            <div class="col-md-3">
                                <label>Periode</label>
                                <select class="form-control" name="period">
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month" selected>Bulan Ini</option>
                                    <option value="custom">Kustom</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Mesin</label>
                                <select class="form-control" name="mesin_id">
                                    <option value="">Semua Mesin</option>
                                    @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}">{{ $machine->nama_mesin }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Kategori</label>
                                <select class="form-control" name="category_id">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="exportDowntime()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="total-downtime">0</h3>
                        <p>Total Downtime (Jam)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="total-events">0</h3>
                        <p>Total Events</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="avg-duration">0</h3>
                        <p>Rata-rata Durasi (Menit)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="resolved-events">0</h3>
                        <p>Events Terselesaikan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Downtime by Category</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Downtime by Machine</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="machineChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Trend Downtime</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Downtime Events Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Downtime Events</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDowntimeModal">
                                <i class="fas fa-plus"></i> Tambah Event
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Mesin</th>
                                        <th>Kategori</th>
                                        <th>Kode</th>
                                        <th>Durasi</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="downtime-table">
                                    @foreach($downtimeEvents as $event)
                                    <tr>
                                        <td>{{ $event->tanggal->format('d/m/Y') }}</td>
                                        <td>{{ $event->mesin->nama_mesin }}</td>
                                        <td>{{ $event->category->name }}</td>
                                        <td>{{ $event->code->code }}</td>
                                        <td>{{ $event->duration_minutes }} menit</td>
                                        <td>
                                            <span class="badge badge-{{ $event->type_color }}">
                                                {{ ucfirst($event->downtime_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $event->status_color }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewDowntimeDetails({{ $event->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editDowntime({{ $event->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $downtimeEvents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Downtime Modal -->
<div class="modal fade" id="addDowntimeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Downtime Event</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addDowntimeForm">
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
                                <label>Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="form-control" name="shift" required>
                                    <option value="shift_1">Shift 1 (07:00-15:00)</option>
                                    <option value="shift_2">Shift 2 (15:00-23:00)</option>
                                    <option value="shift_3">Shift 3 (23:00-07:00)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori Downtime</label>
                                <select class="form-control" name="downtime_category_id" id="category-select" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Downtime</label>
                                <select class="form-control" name="downtime_code_id" id="code-select" required>
                                    <option value="">Pilih Kode</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control" name="downtime_type" required>
                                    <option value="planned">Planned</option>
                                    <option value="unplanned">Unplanned</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Responsibility</label>
                                <select class="form-control" name="responsibility" required>
                                    <option value="operator">Operator</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="external">External</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let categoryChart, machineChart, trendChart;

$(document).ready(function() {
    initializeCharts();
    loadDowntimeData();
    
    // Category change handler
    $('#category-select').on('change', function() {
        loadDowntimeCodes($(this).val());
    });
});

function initializeCharts() {
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#17a2b8', '#6c757d', '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Machine Chart
    const machineCtx = document.getElementById('machineChart').getContext('2d');
    machineChart = new Chart(machineCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Downtime (Jam)',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
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

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Downtime (Jam)',
                data: [],
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.4
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
}

function loadDowntimeData() {
    const formData = $('#filterForm').serialize();
    
    $.get('/oee/production/downtime/data?' + formData, function(data) {
        updateSummary(data.summary);
        updateCharts(data.charts);
    });
}

function updateSummary(summary) {
    $('#total-downtime').text(summary.total_downtime);
    $('#total-events').text(summary.total_events);
    $('#avg-duration').text(summary.avg_duration);
    $('#resolved-events').text(summary.resolved_events);
}

function updateCharts(charts) {
    // Update category chart
    categoryChart.data.labels = charts.category.labels;
    categoryChart.data.datasets[0].data = charts.category.data;
    categoryChart.update();

    // Update machine chart
    machineChart.data.labels = charts.machine.labels;
    machineChart.data.datasets[0].data = charts.machine.data;
    machineChart.update();

    // Update trend chart
    trendChart.data.labels = charts.trend.labels;
    trendChart.data.datasets[0].data = charts.trend.data;
    trendChart.update();
}

function loadDowntimeCodes(categoryId) {
    if (!categoryId) {
        $('#code-select').html('<option value="">Pilih Kode</option>');
        return;
    }
    
    $.get('/oee/downtime-codes/' + categoryId, function(codes) {
        let options = '<option value="">Pilih Kode</option>';
        codes.forEach(function(code) {
            options += `<option value="${code.id}">${code.code} - ${code.name}</option>`;
        });
        $('#code-select').html(options);
    });
}

function viewDowntimeDetails(eventId) {
    window.location.href = `/oee/downtime/${eventId}/details`;
}

function editDowntime(eventId) {
    window.location.href = `/oee/downtime/${eventId}/edit`;
}

function exportDowntime() {
    const formData = $('#filterForm').serialize();
    window.location.href = '/oee/production/downtime/export?' + formData;
}

// Form handlers
$('#filterForm').on('submit', function(e) {
    e.preventDefault();
    loadDowntimeData();
});

$('#addDowntimeForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '/oee/downtime',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                $('#addDowntimeModal').modal('hide');
                location.reload();
            } else {
                alert('Gagal menambahkan downtime event');
            }
        },
        error: function() {
            alert('Terjadi kesalahan');
        }
    });
});
</script>
@endpush
@endsection 