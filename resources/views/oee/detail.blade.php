@extends('oee.app')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">📊 OEE Detail</h2>
                    <p class="text-muted mb-0">{{ $oee->tanggal->format('d M Y') }} - {{ $oee->mesin->nama_mesin ?? 'N/A' }}</p>
                </div>
                <div>
                    <a href="{{ route('oee.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- OEE Metrics --}}
        <div class="col-xl-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>OEE Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h6 class="text-muted">Availability</h6>
                                <h3 class="text-{{ $oee->availability_color }}">{{ number_format($oee->availability_rate, 1) }}%</h3>
                                <div class="progress progress-availability mt-2" style="height: 6px">
                                    <div class="progress-bar bg-{{ $oee->availability_color }}" style="width: <?php echo $oee->availability_rate; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h6 class="text-muted">Performance</h6>
                                <h3 class="text-{{ $oee->performance_color }}">{{ number_format($oee->performance_rate, 1) }}%</h3>
                                <div class="progress progress-performance mt-2" style="height: 6px">
                                    <div class="progress-bar bg-{{ $oee->performance_color }}" style="width: <?php echo $oee->performance_rate; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h6 class="text-muted">Quality</h6>
                                <h3 class="text-{{ $oee->quality_color }}">{{ number_format($oee->quality_rate, 1) }}%</h3>
                                <div class="progress progress-quality mt-2" style="height: 6px">
                                    <div class="progress-bar bg-{{ $oee->quality_color }}" style="width: <?php echo $oee->quality_rate; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h6 class="text-muted">Overall OEE</h6>
                                <h3 class="text-{{ $oee->oee_color }}"><strong>{{ number_format($oee->oee_rate, 1) }}%</strong></h3>
                                <div class="progress progress-oee mt-2" style="height: 6px">
                                    <div class="progress-bar bg-{{ $oee->oee_color }}" style="width: <?php echo $oee->oee_rate; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Production Info --}}
        <div class="col-xl-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Production Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Machine:</strong></td>
                            <td>{{ $oee->mesin->nama_mesin ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Part:</strong></td>
                            <td>{{ $oee->planning->part->no_parts ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>{{ $oee->tanggal->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Planned Time:</strong></td>
                            <td>{{ number_format($oee->planned_time) }} min</td>
                        </tr>
                        <tr>
                            <td><strong>Run Time:</strong></td>
                            <td>{{ number_format($oee->run_time) }} min</td>
                        </tr>
                        <tr>
                            <td><strong>Cycle Time (Std):</strong></td>
                            <td>{{ number_format($oee->cycle_time_std) }} sec</td>
                        </tr>
                        <tr>
                            <td><strong>Cycle Time (Actual):</strong></td>
                            <td>{{ number_format($oee->cycle_time_actual) }} sec</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Output Summary --}}
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Output Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-primary">{{ number_format($oee->actual_output) }}</h4>
                            <small class="text-muted">Total Output</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success">{{ number_format($oee->good_output) }}</h4>
                            <small class="text-muted">Good Output</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger">{{ number_format($oee->ng_output) }}</h4>
                            <small class="text-muted">NG Output</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NG Analysis --}}
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>NG Analysis</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Material NG:</td>
                            <td><span class="badge badge-danger">{{ number_format($oee->ng_material ?? 0) }}</span></td>
                        </tr>
                        <tr>
                            <td>Process NG:</td>
                            <td><span class="badge badge-warning">{{ number_format($oee->ng_process ?? 0) }}</span></td>
                        </tr>
                        <tr>
                            <td>Machine NG:</td>
                            <td><span class="badge badge-info">{{ number_format($oee->ng_machine ?? 0) }}</span></td>
                        </tr>
                        <tr>
                            <td>Operator NG:</td>
                            <td><span class="badge badge-secondary">{{ number_format($oee->ng_operator ?? 0) }}</span></td>
                        </tr>
                        <tr>
                            <td>Other NG:</td>
                            <td><span class="badge badge-dark">{{ number_format($oee->ng_other ?? 0) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection