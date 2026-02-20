@extends('mesin.app')

@section('title', 'Riwayat Mesin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/history-page.css') }}">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Riwayat Status Mesin</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Log Downtime & Aktivitas Mesin</h3>
                <div class="card-tools">
                    <a href="{{ route('dashboard.inject.history.print', request()->all()) }}" target="_blank" class="btn btn-tool">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                    <button type="button" class="btn btn-tool" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Print Header (Visible only in print) -->
                <div class="print-header" style="display: none;">
                    <h2>PT. MADA WIKRI TUNGGAL</h2>
                    <p>LAPORAN RIWAYAT STATUS MESIN</p>
                    <p style="font-size: 10pt; margin-top: 5px;">Tanggal: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                </div>

                <!-- Filter Date Range & Shift -->
                <div class="card mb-4 no-print shadow-sm filter-section">
                    <div class="card-body py-4">
                        <form id="historyFilterForm" action="{{ route('dashboard.inject.history') }}" method="GET">
                            <input type="hidden" name="mesin_id" value="{{ request('mesin_id') }}">
                            <div class="row align-items-end g-3">
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-2 d-block">Dari Tanggal</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-right-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                            </div>
                                            <input type="date" name="start_date" class="form-control border-left-0" value="{{ $startDate }}" onchange="this.form.submit()">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-2 d-block">Sampai Tanggal</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-right-0"><i class="fas fa-calendar-check text-success"></i></span>
                                            </div>
                                            <input type="date" name="end_date" class="form-control border-left-0" value="{{ $endDate }}" onchange="this.form.submit()">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-2 d-block">Filter Shift</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-right-0"><i class="fas fa-clock text-warning"></i></span>
                                            </div>
                                            <select name="shift" class="form-control border-left-0" onchange="this.form.submit()">
                                                <option value="">Semua Shift</option>
                                                <option value="pagi" {{ request('shift') == 'pagi' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                                                <option value="siang" {{ request('shift') == 'siang' ? 'selected' : '' }}>Shift 2 (Siang)</option>
                                                <option value="malam" {{ request('shift') == 'malam' ? 'selected' : '' }}>Shift 3 (Malam)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12 col-sm-6 text-right">
                                    <div class="d-flex gap-2 h-100 justify-content-end align-items-end">
                                        {{-- Hidden Mesin ID for Filter Persistence --}}
                                        <input type="hidden" name="mesin_id" value="{{ request('mesin_id') }}">

                                        @if(request('mesin_id') || request('shift'))
                                        <a href="{{ route('dashboard.inject.history') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm font-weight-bold d-flex align-items-center justify-content-center mr-2">
                                            <i class="fas fa-times mr-1"></i> Reset
                                        </a>
                                        @endif
                                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" style="min-height: 31px;">
                                            <i class="fas fa-filter mr-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Server Time Reference for JS -->
                @php
                $serverTime = \Carbon\Carbon::now()->format('Y-m-d\TH:i:s');
                @endphp
                <div id="server-time-ref" data-value="{{ $serverTime }}" style="display: none;"></div>

                @php
                // Show all activity in the history log by default, including Running/Aktif
                $filteredEvents = $events;
                $no = 1;
                @endphp

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box shadow-sm mb-2">
                            <span class="info-box-icon bg-success"><i class="fas fa-play-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Runtime</span>
                                <span class="info-box-number summary-total" data-type="aktif" data-base="{{ $baseRuntimeSeconds }}">
                                    @php
                                    $h = floor($totalRuntimeSeconds / 3600);
                                    $m = floor(($totalRuntimeSeconds % 3600) / 60);
                                    $s = $totalRuntimeSeconds % 60;
                                    @endphp
                                    {{ $h }}j {{ $m }}m {{ $s }}d
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box shadow-sm mb-2">
                            <span class="info-box-icon bg-danger"><i class="fas fa-tools"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Breakdown</span>
                                <span class="info-box-number summary-total" data-type="maintenance" data-base="{{ $baseMaintenanceSeconds }}">
                                    @php
                                    $h = floor($totalMaintenanceSeconds / 3600);
                                    $m = floor(($totalMaintenanceSeconds % 3600) / 60);
                                    $s = $totalMaintenanceSeconds % 60;
                                    @endphp
                                    {{ $h }}j {{ $m }}m {{ $s }}d
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box shadow-sm mb-2">
                            <span class="info-box-icon bg-dark text-white"><i class="fas fa-power-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Off (No Plan)</span>
                                <span class="info-box-number summary-total" data-type="offline" data-base="{{ $baseOfflineSeconds }}">
                                    @php
                                    $h = floor($totalOfflineSeconds / 3600);
                                    $m = floor(($totalOfflineSeconds % 3600) / 60);
                                    $s = $totalOfflineSeconds % 60;
                                    @endphp
                                    {{ $h }}j {{ $m }}m {{ $s }}d
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box shadow-sm mb-2">
                            <span class="info-box-icon bg-warning text-dark"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Stand By</span>
                                <span class="info-box-number summary-total" data-type="standby" data-base="{{ $baseStandbySeconds }}">
                                    @php
                                    $h = floor($totalStandbySeconds / 3600);
                                    $m = floor(($totalStandbySeconds % 3600) / 60);
                                    $s = $totalStandbySeconds % 60;
                                    @endphp
                                    {{ $h }}j {{ $m }}m {{ $s }}d
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Per Status Breakdown -->
                <div class="card card-outline card-success mb-4">
                    <div class="card-header py-2">
                        <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-list-ul mr-1"></i> Ringkasan Durasi Per Status (Status Breakdown)</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-3">Status / Alasan</th>
                                        <th class="text-center">Total Durasi</th>
                                        <th class="text-center">Persentase (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $combinedTotals = [];
                                    foreach($reasonTotals as $type => $minutes) {
                                    $category = 'standby';
                                    if (in_array($type, ['aktif', 'running'])) $category = 'aktif';
                                    elseif (in_array($type, ['maintenance', 'breakdown', 'downtime'])) $category = 'maintenance';
                                    elseif (in_array($type, ['offline', 'tidak aktif'])) $category = 'offline';

                                    if (!isset($combinedTotals[$category])) {
                                    $combinedTotals[$category] = 0;
                                    }
                                    $combinedTotals[$category] += $minutes;
                                    }
                                    $totalDayMinutes = collect($combinedTotals)->sum();
                                    @endphp
                                    @foreach($combinedTotals as $category => $minutes)
                                    @php
                                    $statusLabel = 'Stand By';
                                    $statusColor = 'warning';
                                    switch($category) {
                                    case 'aktif':
                                    $statusLabel = 'Running'; $statusColor = 'success'; break;
                                    case 'maintenance':
                                    $statusLabel = 'Breakdown'; $statusColor = 'danger'; break;
                                    case 'offline':
                                    $statusLabel = 'Off'; $statusColor = 'dark'; break;
                                    }
                                    $h = floor($minutes / 3600);
                                    $m = floor(($minutes % 3600) / 60);
                                    $s = $minutes % 60;
                                    $percentage = $totalDayMinutes > 0 ? round(($minutes / $totalDayMinutes) * 100, 1) : 0;
                                    $percentageStyle = 'width: ' . $percentage . '%;';
                                    @endphp
                                    <tr>
                                        <td class="pl-3">
                                            <span class="badge badge-{{ $statusColor }} px-2">{{ strtoupper($statusLabel) }}</span>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            {{ $h }}j {{ $m }}m {{ $s }}d
                                        </td>
                                        <td class="text-center">
                                            <div class="progress progress-xs mb-1" style="height: 10px;">
                                                <div class="progress-bar bg-{{ $statusColor }}" style="<?php echo $percentageStyle; ?>"></div>
                                            </div>
                                            <small>{{ $percentage }}%</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Per Machine Grid -->
                <div class="card card-outline card-info mb-4 border-0 shadow-sm">
                    <div class="card-header py-2 bg-transparent border-bottom">
                        <h3 class="card-title text-info font-weight-bold" style="font-size: 1rem;"><i class="fas fa-th mr-1"></i> Ringkasan Aktivitas Per Mesin</h3>
                        <div class="card-tools">
                            <span class="badge badge-info mr-2 shadow-sm">Klik card untuk detail</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus text-info"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="machine-grid-compact">
                            @foreach($machineTotals as $total)
                            @php
                            $statusColor = 'secondary';
                            $displayStatus = strtoupper($total['status'] ?? 'OFF');
                            $timerType = 'aktif';
                            $baseSeconds = $total['base_runtime'];
                            $currentDuration = $total['runtime'];

                            switch(strtolower($total['status'] ?? '')) {
                            case 'aktif': case 'running':
                            $statusColor = 'success';
                            $displayStatus = 'RUNNING';
                            $timerType = 'aktif';
                            $baseSeconds = $total['base_runtime'];
                            $currentDuration = $total['runtime'];
                            break;
                            case 'stop': case 'standby': case 'setup': case 'setdown':
                            $statusColor = 'warning';
                            $displayStatus = ($total['status'] === 'stop' ? 'STAND BY' : strtoupper($total['status']));
                            $timerType = 'standby';
                            $baseSeconds = $total['base_standby'] ?? 0;
                            $currentDuration = $total['standby'] ?? 0;
                            break;
                            case 'maintenance': case 'breakdown': case 'downtime':
                            $statusColor = 'danger';
                            $displayStatus = 'BREAKDOWN';
                            $timerType = 'maintenance';
                            $baseSeconds = $total['base_maintenance'] ?? 0;
                            $currentDuration = $total['maintenance'] ?? 0;
                            break;
                            case 'offline': case 'tidak aktif':
                            $statusColor = 'dark';
                            $displayStatus = 'OFF';
                            $timerType = 'offline';
                            $baseSeconds = ($total['base_offline'] ?? 0);
                            $currentDuration = ($total['offline'] ?? 0);
                            break;
                            }
                            @endphp
                            <div class="machine-card-compact border-{{ $statusColor }} machine-card-trigger shadow-sm"
                                style="cursor: pointer;"
                                data-machine-id="{{ $total['id'] }}"
                                data-machine-name="{{ $total['nama_mesin'] }}"
                                data-status="{{ strtoupper($total['status']) }}"
                                data-runtime="{{ $total['runtime'] }}"
                                data-maintenance="{{ $total['maintenance'] }}"
                                data-standby="{{ $total['standby'] }}"
                                data-offline="{{ $total['offline'] }}"
                                data-color="{{ $statusColor }}">

                                <div class="machine-name-compact" title="{{ $total['nama_mesin'] }} | Status: {{ $displayStatus }}{{ $total['stop_reason'] ? ' ('.$total['stop_reason'].')' : '' }}">
                                    {{ $total['nama_mesin'] }}
                                </div>

                                <div class="machine-runtime-compact machine-total"
                                    data-machine-id="{{ $total['id'] }}"
                                    data-type="{{ $timerType }}"
                                    data-base="{{ $baseSeconds }}">
                                    @php
                                    $h = floor($currentDuration / 3600);
                                    $m = floor(($currentDuration % 3600) / 60);
                                    $s = $currentDuration % 60;
                                    @endphp
                                    {{ $h }}j {{ $m }}m {{ $s }}d
                                </div>

                                <div class="status-indicator">
                                    <i class="fas fa-circle text-{{ $statusColor }}"></i> {{ $displayStatus }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Master counters for real-time tracking (Hidden) -->
                <!-- We keep ALL ongoing events here to ensure timers work even if the log row is on Page 2+ -->
                <div id="master-counters" style="display: none;">
                    @foreach($events->filter(function($e) { return is_null($e->end_time); }) as $event)
                    @php
                    $eventDate = is_string($event->tanggal) ? $event->tanggal : $event->tanggal->format('Y-m-d');
                    $start = \Carbon\Carbon::parse($eventDate . ' ' . $event->start_time);
                    $selectedDateObj = \Carbon\Carbon::parse($date)->startOfDay();
                    $displayStart = $start->lt($selectedDateObj) ? $selectedDateObj : $start;
                    $jsDate = $displayStart->format('Y-m-d\TH:i:s');
                    @endphp
                    <span class="duration-counter" data-start="{{ $jsDate }}" data-machine-id="{{ $event->mesin_id }}" data-type="{{ $event->downtime_type }}"></span>
                    @endforeach
                </div>

                <h5 class="mb-3 text-bold"><i class="fas fa-history mr-1"></i> Log Aktivitas Mesin (Detailed History)</h5>
                <!-- Detailed Log Table -->
                <div class="table-responsive shadow-sm rounded border">
                    <table id="historyTable" class="table table-hover mb-0 w-100">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-nowrap">Nama Mesin</th>
                                <th class="text-center text-nowrap">Status</th>
                                <th class="text-nowrap">PIC</th>
                                <th class="text-center text-nowrap">Waktu Mulai</th>
                                <th class="text-center text-nowrap">Waktu Selesai</th>
                                <th class="text-nowrap">Durasi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredEvents as $event)
                            @php
                            $statusColor = 'secondary';
                            $statusLabel = 'Stand By';

                            switch($event->downtime_type) {
                            case 'aktif': case 'running':
                            $statusColor = 'success'; $statusLabel = 'Running'; break;
                            case 'maintenance': case 'breakdown':
                            $statusColor = 'danger'; $statusLabel = 'Breakdown'; break;
                            case 'offline': case 'tidak aktif':
                            $statusColor = 'dark'; $statusLabel = 'Off'; break;
                            default:
                            $statusColor = 'warning'; $statusLabel = 'Stand By'; break;
                            }

                            $eventDate = is_string($event->tanggal) ? $event->tanggal : $event->tanggal->format('Y-m-d');
                            $eventStart = \Carbon\Carbon::parse($eventDate . ' ' . $event->start_time);

                            // Define the selected date range for clamping
                            $rangeStart = \Carbon\Carbon::parse($date)->startOfDay();
                            $rangeEndFinal = \Carbon\Carbon::parse($date)->endOfDay();

                            if ($event->end_time) {
                            $endDateStr = $event->updated_at->format('Y-m-d');
                            // If the event ends on a different day than it started, ensure the end date is correct
                            if ($endDateStr < $eventDate) {
                                $endDateStr=$eventDate;
                                }
                                $eventEnd=\Carbon\Carbon::parse($endDateStr . ' ' . $event->end_time);
                                if ($eventEnd->lt($eventStart)) {
                                $eventEnd->addDay(); // Handle overnight events
                                }
                                } else {
                                // For ongoing events, the end is considered the end of the selected day
                                $eventEnd = $rangeEndFinal->copy();
                                }

                                // Clamp the event's start and end times to the selected date range
                                $clampedStart = $eventStart->max($rangeStart);
                                $clampedEnd = $eventEnd->min($rangeEndFinal);

                                $diffSeconds = 0;
                                if ($clampedStart->lt($clampedEnd)) {
                                $diffSeconds = $clampedStart->diffInSeconds($clampedEnd);
                                }

                                $h = floor($diffSeconds / 3600);
                                $m = floor(($diffSeconds % 3600) / 60);
                                $s = $diffSeconds % 60;

                                $duration = is_null($event->end_time)
                                ? '<span class="log-duration-counter" data-start="' . $clampedStart->format('Y-m-d\TH:i:s') . '">' . "{$h}j {$m}m {$s}d (Berjalan)" . '</span>'
                                : "{$h}j {$m}m {$s}d";

                                $startDisplay = $clampedStart->format('H:i:s');
                                if ($eventStart->lt($rangeStart)) {
                                $startDisplay .= ' <br><span class="badge badge-soft-info" style="font-size: 10px;">Lanjutan Kemarin</span>';
                                }

                                $endDisplay = '-';
                                if ($event->end_time) {
                                $endDisplay = $clampedEnd->format('H:i:s');
                                if ($eventEnd->gt($rangeEndFinal)) {
                                $endDisplay .= ' <br><span class="badge badge-soft-info" style="font-size: 10px;">Berlanjut Besok</span>';
                                }
                                }
                                @endphp
                                <tr class="align-middle">
                                    <td class="text-center align-middle">{{ $no++ }}</td>
                                    <td class="font-weight-bold align-middle text-nowrap text-{{ $statusColor }}">
                                        {{ $event->mesin->nama_mesin ?? '-' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        <div class="d-flex flex-column">
                                            @if($event->user)
                                            <span class="font-weight-bold">{{ $event->user->name ?? $event->user->nama }}</span>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ strtolower($event->user->role ?? 'User') }}</small>
                                            @else
                                            <span class="font-weight-bold text-muted">System / Auto</span>
                                            <small class="text-muted" style="font-size: 0.75rem;">Monitoring</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">{!! $startDisplay !!}</td>
                                    <td class="text-center align-middle">{!! $endDisplay !!}</td>
                                    <td class="text-{{ $statusColor }} font-weight-bold align-middle text-nowrap">
                                        {!! $duration !!}
                                    </td>
                                    <td class="align-middle">{{ $event->description ?? $event->keterangan ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 align-middle">
                                        <i class="fas fa-info-circle text-muted mb-2" style="font-size: 2rem;"></i>
                                        <p class="text-muted m-0">Tidak ada data downtime untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" class="btn btn-primary rounded-circle shadow-lg" style="position: fixed; bottom: 30px; right: 30px; display: none; z-index: 9999; width: 45px; height: 45px; padding: 0; line-height: 45px;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Machine Detail Modal -->
<div class="modal fade" id="machineDetailModal" tabindex="-1" role="dialog" aria-labelledby="machineDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="machineDetailModalLabel">
                    <i class="fas fa-info-circle mr-2"></i> Detail Aktivitas <span id="modalMachineName" class="font-weight-bold ml-1"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" id="modal-active-mesin-id">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm border-bottom border-success" style="border-width: 3px !important;">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Running</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="modalRuntime">0j 0m</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm border-bottom border-danger" style="border-width: 3px !important;">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Breakdown</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="modalMaintenance">0j 0m</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm border-bottom border-warning" style="border-width: 3px !important;">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Stand By</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="modalStandby">0j 0m</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm border-bottom border-dark" style="border-width: 3px !important;">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">OFF / Stop</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="modalOffline">0j 0m</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i> Log Aktivitas Mesin Terpilih</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" id="modalLogTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Waktu Start</th>
                                    <th>Waktu Selesai</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-info btn-sm" id="btnFilterTable">
                    <i class="fas fa-search mr-1"></i> Lihat di Tabel Utama
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
$currentUserName = auth()->user()->nama ?? 'Guest';
$currentUserRole = auth()->user()->role ?? 'Viewer';
@endphp
<div id="history-log-data" data-logs="{{ base64_encode(json_encode($historyLog)) }}" style="display: none;"></div>

<input type="hidden" id="current-user-name" value="{{ $currentUserName }}">
<input type="hidden" id="current-user-role" value="{{ $currentUserRole }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="{{ asset('js/history-page.js') }}?v={{ time() }}"></script>
@endpush