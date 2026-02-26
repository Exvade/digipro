<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Riwayat Status Mesin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        @page {
            size: A4;
            margin: 25mm 15mm 15mm 15mm;
        }

        body {
            background-color: white;
            color: black;
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 9pt;
        }

        .wrapper {
            width: 96%;
            margin: 5mm auto 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000 !important;
            padding: 5px 4px;
            vertical-align: middle;
        }

        th {
            background-color: transparent !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .print-header {
            display: flex !important;
            border: 2px solid black;
            margin-bottom: 20px;
        }

        .logo-section {
            flex: 0 0 120px;
            border-right: 2px solid black;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .title-section {
            flex: 1;
            padding: 10px;
            text-align: center;
        }

        .summary-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 2px solid black;
            margin-bottom: 20px;
        }

        .summary-item {
            border-right: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 10pt;
            font-weight: bold;
        }

        .section-title {
            background: transparent;
            color: black;
            padding: 5px 0;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 10pt;
            border-bottom: 2px solid black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .machine-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            margin-bottom: 20px;
        }

        .machine-item {
            border: 1px solid #ccc;
            padding: 5px;
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
        }

        .badge {
            border: 1px solid #000;
            color: black !important;
            background: transparent !important;
            font-weight: bold;
            font-size: 7pt;
        }

        @media print {
            .no-print {
                display: none;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="print-header">
            <div class="logo-section">
                <img src="{{ asset('images/logomwthijau.png') }}" alt="Logo MWT" style="max-width: 100%; max-height: 60px;">
            </div>
            <div class="title-section">
                <h4 class="font-weight-bold m-0" style="font-size: 18pt;">LAPORAN RIWAYAT STATUS MESIN</h4>
                <div class="mt-1">
                    <span class="mr-3"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</span>
                    @if(request('shift'))
                    <span class="ml-3"><strong>Shift:</strong> {{ ucfirst(request('shift')) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-label">Total Runtime</div>
                <div class="summary-value">
                    @php
                    $h = floor($totalRuntimeSeconds / 3600);
                    $m = floor(($totalRuntimeSeconds % 3600) / 60);
                    $s = $totalRuntimeSeconds % 60;
                    @endphp
                    {{ $h }}j {{ $m }}m {{ $s }}d
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Maintenance</div>
                <div class="summary-value">
                    @php
                    $h = floor($totalMaintenanceSeconds / 3600);
                    $m = floor(($totalMaintenanceSeconds % 3600) / 60);
                    $s = $totalMaintenanceSeconds % 60;
                    @endphp
                    {{ $h }}j {{ $m }}m {{ $s }}d
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Offline</div>
                <div class="summary-value">
                    @php
                    $h = floor($totalOfflineSeconds / 3600);
                    $m = floor(($totalOfflineSeconds % 3600) / 60);
                    $s = $totalOfflineSeconds % 60;
                    @endphp
                    {{ $h }}j {{ $m }}m {{ $s }}d
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Stand By</div>
                <div class="summary-value">
                    @php
                    $h = floor($totalStandbySeconds / 3600);
                    $m = floor(($totalStandbySeconds % 3600) / 60);
                    $s = $totalStandbySeconds % 60;
                    @endphp
                    {{ $h }}j {{ $m }}m {{ $s }}d
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="section-title">Breakdown Durasi Per Status</div>
        <table class="table table-sm">
            <thead>
                <tr style="background-color: #f4f4f4;">
                    <th class="text-left">Status / Alasan</th>
                    <th class="text-center" style="width: 150px;">Total Durasi</th>
                    <th class="text-center" style="width: 100px;">Persentase</th>
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
                switch($category) {
                case 'aktif': $statusLabel = 'Running'; break;
                case 'maintenance': $statusLabel = 'Breakdown'; break;
                case 'offline': $statusLabel = 'Off'; break;
                }
                $h = floor($minutes / 3600);
                $m = floor(($minutes % 3600) / 60);
                $s = $minutes % 60;
                $percentage = $totalDayMinutes > 0 ? round(($minutes / $totalDayMinutes) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="text-left font-weight-bold">{{ strtoupper($statusLabel) }}</td>
                    <td class="text-center">{{ $h }}j {{ $m }}m {{ $s }}d</td>
                    <td class="text-center">{{ $percentage }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Machine Summary Grid -->
        <div class="section-title">Runtime Per Mesin</div>
        <div class="machine-grid">
            @foreach($machineTotals as $total)
            @php
            $displayStatus = strtoupper($total['status'] ?? 'OFF');
            $statusColor = 'secondary';
            $currentDuration = $total['runtime'];
            $stopReason = $total['stop_reason'] ?? '';

            switch(strtolower($total['status'] ?? '')) {
            case 'aktif': case 'running':
            $displayStatus = 'RUNNING';
            $statusColor = 'success';
            $currentDuration = $total['runtime'];
            break;
            case 'stop': case 'standby': case 'setup': case 'setdown':
            $displayStatus = ($total['status'] === 'stop' ? 'STAND BY' : strtoupper($total['status']));
            $statusColor = 'warning';
            $currentDuration = $total['standby'] ?? 0;
            break;
            case 'maintenance': case 'breakdown': case 'downtime':
            $displayStatus = 'BREAKDOWN';
            $statusColor = 'danger';
            $currentDuration = $total['maintenance'] ?? 0;
            break;
            case 'offline': case 'tidak aktif':
            $displayStatus = 'OFF';
            $statusColor = 'dark';
            $currentDuration = $total['offline'] ?? 0;
            break;
            }
            $h = floor($currentDuration / 3600);
            $m = floor(($currentDuration % 3600) / 60);
            $s = $currentDuration % 60;

            $borderColor = $statusColor == 'success' ? '#28a745' : ($statusColor == 'warning' ? '#ffc107' : ($statusColor == 'danger' ? '#dc3545' : '#343a40'));
            $cardStyle = "border-left: 3px solid $borderColor;";
            @endphp
            <div class="machine-item" style="<?php echo $cardStyle; ?>">
                <div>
                    <span class="font-weight-bold">{{ $total['nama_mesin'] }}</span><br>
                    <small style="font-size: 7pt; color: #666;">
                        {{ $displayStatus }}{{ $stopReason ? ' ('.$stopReason.')' : '' }}
                    </small>
                </div>
                <div class="text-right">
                    <span class="font-weight-bold">{{ $h }}j {{ $m }}m {{ $s }}d</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Detail Table -->
        <div class="section-title">Log Aktivitas Mesin (Detailed Log)</div>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th style="width: 40px" class="text-center">No</th>
                    <th>Nama Mesin</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th class="text-center">PIC</th>
                    <th class="text-center" style="width: 80px;">Mulai</th>
                    <th class="text-center" style="width: 80px;">Selesai</th>
                    <th class="text-center" style="width: 120px;">Durasi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                $no = 1;
                $nonRunningEvents = $events->filter(function($e) {
                return !in_array($e->downtime_type, ['aktif', 'running']);
                });
                @endphp
                @forelse($nonRunningEvents as $event)
                @php
                $eventDate = is_string($event->tanggal) ? $event->tanggal : $event->tanggal->format('Y-m-d');
                $start = \Carbon\Carbon::parse($eventDate . ' ' . $event->start_time);
                $end = null;
                $duration = '-';

                // Determine clip boundary
                $rangeEndFinal = \Carbon\Carbon::parse($endDate)->isToday() ? \Carbon\Carbon::now() : \Carbon\Carbon::parse($endDate)->endOfDay();

                if ($event->end_time) {
                $end = \Carbon\Carbon::parse($eventDate . ' ' . $event->end_time);
                if ($end->lt($start)) { $end->addDay(); }

                // Actual duration (clipped by range)
                $actualStart = $start->max(\Carbon\Carbon::parse($startDate)->startOfDay());
                $actualEnd = $end->min($rangeEndFinal);

                if ($actualStart->lt($actualEnd)) {
                $totalSeconds = $actualStart->diffInSeconds($actualEnd);
                $h = floor($totalSeconds / 3600);
                $m = floor(($totalSeconds % 3600) / 60);
                $s = $totalSeconds % 60;
                $duration = "{$h}j {$m}m {$s}d";
                }
                } else {
                $actualStart = $start->max(\Carbon\Carbon::parse($startDate)->startOfDay());
                if ($actualStart->lt($rangeEndFinal)) {
                $totalSeconds = $actualStart->diffInSeconds($rangeEndFinal);
                $h = floor($totalSeconds / 3600);
                $m = floor(($totalSeconds % 3600) / 60);
                $s = $totalSeconds % 60;
                $duration = "{$h}j {$m}m {$s}d" . ($isToday ? " (Berjalan)" : "");
                }
                }

                $statusLabel = 'Stand By';
                switch($event->downtime_type) {
                case 'aktif': case 'running': $statusLabel = 'Running'; break;
                case 'maintenance': $statusLabel = 'Breakdown'; break;
                case 'offline': case 'tidak aktif': $statusLabel = 'Off'; break;
                default: $statusLabel = 'Stand By'; break;
                }
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="font-weight-bold">{{ $event->mesin->nama_mesin ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge px-2">{{ strtoupper($statusLabel) }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column" style="line-height: 1.2;">
                            <span class="font-weight-bold">{{ $event->user?->name ?? ($event->user?->nama ?? '-') }}</span><br>
                            <small class="text-muted" style="font-size: 7pt; color: #666;">{{ strtolower($event->user?->role ?? '-') }}</small>
                        </div>
                    </td>
                    <td class="text-center">{{ $start->format('H:i:s') }}</td>
                    <td class="text-center">{{ $end ? $end->format('H:i:s') : '-' }}</td>
                    <td class="text-center font-weight-bold">{{ $duration }}</td>
                    <td>{{ ($event->description ?? $event->keterangan ?? '-') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-3">Tidak ada data downtime untuk periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="mt-4 d-flex justify-content-between">
            <small>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</small>
            <div style="width: 200px; text-align: center;">
                <p class="mb-5">Disetujui Oleh,</p>
                <p class="font-weight-bold">( ____________________ )</p>
                <p>Kepala Bagian</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>