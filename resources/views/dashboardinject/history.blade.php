@extends('mesin.app')

@section('title', 'Riwayat Status Mesin')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('css/history-page.css') }}?v={{ filemtime(public_path('css/history-page.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shadow-inner flex-shrink-0">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Riwayat Status Mesin
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                    Downtime & Activity
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Log durasi operasional, breakdown, stand by, dan status mesin real-time
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.inject.history.print', request()->all()) }}" target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-sm transition-all cursor-pointer">
            <i class="fas fa-print text-xs text-indigo-600 dark:text-indigo-400"></i>
            <span>Cetak Laporan</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Print Header (Visible only in print) -->
    <div class="print-header hidden">
        <h2>PT. MADA WIKRI TUNGGAL</h2>
        <p>LAPORAN RIWAYAT STATUS MESIN</p>
        <p style="font-size: 10pt; margin-top: 5px;">Tanggal: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
    </div>

    <!-- Filter Date Range & Shift Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm p-4 sm:p-5 no-print">
        <form id="historyFilterForm" action="{{ route('dashboard.inject.history') }}" method="GET" class="space-y-3">
            <input type="hidden" name="mesin_id" value="{{ request('mesin_id') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Dari Tanggal -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                        Dari Tanggal
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500 text-xs">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"
                            class="w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Sampai Tanggal -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                        Sampai Tanggal
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-emerald-500 text-xs">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                            class="w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Filter Shift -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                        Filter Shift
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-amber-500 text-xs">
                            <i class="fas fa-clock"></i>
                        </span>
                        <select name="shift" onchange="this.form.submit()"
                            class="w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Shift</option>
                            <option value="pagi" {{ request('shift') == 'pagi' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                            <option value="siang" {{ request('shift') == 'siang' ? 'selected' : '' }}>Shift 2 (Siang)</option>
                            <option value="malam" {{ request('shift') == 'malam' ? 'selected' : '' }}>Shift 3 (Malam)</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 justify-end">
                    @if(request('mesin_id') || request('shift'))
                    <a href="{{ route('dashboard.inject.history') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all cursor-pointer">
                        <i class="fas fa-times"></i>
                        <span>Reset</span>
                    </a>
                    @endif
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 shadow-sm shadow-indigo-600/30 transition-all cursor-pointer">
                        <i class="fas fa-filter"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>
        </form>
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

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Runtime -->
        <div class="kpi-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-history-filter="aktif" title="Klik untuk memfilter log Running">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Runtime
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="summary-total text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-type="aktif" data-base="{{ $baseRuntimeSeconds }}">
                        @php
                        $h = floor($totalRuntimeSeconds / 3600);
                        $m = floor(($totalRuntimeSeconds % 3600) / 60);
                        $s = $totalRuntimeSeconds % 60;
                        @endphp
                        {{ $h }}j {{ $m }}m {{ $s }}d
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        <!-- Total Breakdown -->
        <div class="kpi-card kpi-card-rose bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-history-filter="maintenance" title="Klik untuk memfilter log Breakdown">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Breakdown
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="summary-total text-xl sm:text-2xl font-black font-mono text-rose-600 dark:text-rose-400" data-type="maintenance" data-base="{{ $baseMaintenanceSeconds }}">
                        @php
                        $h = floor($totalMaintenanceSeconds / 3600);
                        $m = floor(($totalMaintenanceSeconds % 3600) / 60);
                        $s = $totalMaintenanceSeconds % 60;
                        @endphp
                        {{ $h }}j {{ $m }}m {{ $s }}d
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-600 rounded-b-2xl hidden"></div>
        </div>

        <!-- Total Off (No Plan) -->
        <div class="kpi-card kpi-card-slate bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-history-filter="offline" title="Klik untuk memfilter log Off">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Off (No Plan)
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="summary-total text-xl sm:text-2xl font-black font-mono text-slate-700 dark:text-slate-300" data-type="offline" data-base="{{ $baseOfflineSeconds }}">
                        @php
                        $h = floor($totalOfflineSeconds / 3600);
                        $m = floor(($totalOfflineSeconds % 3600) / 60);
                        $s = $totalOfflineSeconds % 60;
                        @endphp
                        {{ $h }}j {{ $m }}m {{ $s }}d
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-power-off"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-500 to-slate-600 rounded-b-2xl hidden"></div>
        </div>

        <!-- Total Stand By -->
        <div class="kpi-card kpi-card-amber bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-history-filter="standby" title="Klik untuk memfilter log Stand By">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Stand By
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="summary-total text-xl sm:text-2xl font-black font-mono text-amber-500 dark:text-amber-400" data-type="standby" data-base="{{ $baseStandbySeconds }}">
                        @php
                        $h = floor($totalStandbySeconds / 3600);
                        $m = floor(($totalStandbySeconds % 3600) / 60);
                        $s = $totalStandbySeconds % 60;
                        @endphp
                        {{ $h }}j {{ $m }}m {{ $s }}d
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-b-2xl hidden"></div>
        </div>
    </div>

    <!-- Ringkasan Durasi Per Status (Status Breakdown) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Ringkasan Durasi Per Status</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Proporsi durasi operasional harian terhadap total waktu</p>
                </div>
            </div>
        </div>
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Status / Alasan</th>
                        <th class="px-5 py-3.5 text-center">Total Durasi</th>
                        <th class="px-5 py-3.5 text-center" style="width: 35%;">Persentase (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
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
                    $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800';
                    $barBg = 'bg-amber-500';

                    switch($category) {
                        case 'aktif':
                            $statusLabel = 'Running';
                            $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border-emerald-800';
                            $barBg = 'bg-emerald-500';
                            break;
                        case 'maintenance':
                            $statusLabel = 'Breakdown';
                            $badgeBg = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/70 dark:text-rose-300 dark:border-rose-800';
                            $barBg = 'bg-rose-500';
                            break;
                        case 'offline':
                            $statusLabel = 'Off';
                            $badgeBg = 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                            $barBg = 'bg-slate-500';
                            break;
                    }
                    $h = floor($minutes / 3600);
                    $m = floor(($minutes % 3600) / 60);
                    $s = $minutes % 60;
                    $percentage = $totalDayMinutes > 0 ? round(($minutes / $totalDayMinutes) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeBg }}">
                                {{ strtoupper($statusLabel) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $h }}j {{ $m }}m {{ $s }}d
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $barBg }} transition-all duration-500" style="width: {{ $percentage }}%;"></div>
                                </div>
                                <span class="font-mono font-bold text-slate-600 dark:text-slate-400 w-12 text-right">{{ $percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ringkasan Aktivitas Per Mesin (Machine Grid) -->
    <div id="machineGridCard" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm">
                    <i class="fas fa-th-large"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Ringkasan Aktivitas Per Mesin</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Status dan timer real-time per mesin injeksi</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60">
                <i class="fas fa-mouse-pointer mr-1"></i> Klik card mesin untuk opsi detail
            </span>
        </div>
        <div class="p-4 sm:p-5">
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

    <!-- Log Aktivitas Mesin Detailed Table Card -->
    <div id="historyTableCard" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-list-check"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Log Aktivitas Mesin (Detailed History)</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Histori transisi status, downtime, operator, dan durasi mesin</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Total: {{ count($filteredEvents) }} Log
            </span>
        </div>

        <div class="p-4 sm:p-5">
            <table id="historyTable" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">No</th>
                        <th>Nama Mesin</th>
                        <th class="text-center">Status</th>
                        <th>PIC</th>
                        <th class="text-center">Waktu Mulai</th>
                        <th class="text-center">Waktu Selesai</th>
                        <th class="text-center">Durasi</th>
                        <th>Keterangan</th>
                        <th class="text-center" style="width: 90px;">Aksi</th>
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

                    // Khusus untuk panggil bantuan (Andon)
                    if (str_contains(strtolower($event->title), 'call:')) {
                        $statusLabel = 'Andon Call';
                        if ($event->status === 'pending') {
                            $statusColor = 'danger';
                            $statusLabel = 'SOS: Pending';
                        } elseif ($event->status === 'escalated') {
                            $statusColor = 'warning';
                            $statusLabel = 'SOS: Escalated';
                        } elseif ($event->status === 'resolved') {
                            $statusColor = 'success';
                            $statusLabel = 'SOS: Resolved';
                        }
                    }

                    $eventDate = is_string($event->tanggal) ? $event->tanggal : $event->tanggal->format('Y-m-d');
                    $eventStart = \Carbon\Carbon::parse($eventDate . ' ' . $event->start_time);

                    // Define the selected date range for clamping
                    $rangeStart = \Carbon\Carbon::parse($date)->startOfDay();
                    $rangeEndFinal = \Carbon\Carbon::parse($date)->endOfDay();

                    if ($event->end_time) {
                        $endDateStr = $event->updated_at->format('Y-m-d');
                        if ($endDateStr < $eventDate) {
                            $endDateStr = $eventDate;
                        }
                        $eventEnd = \Carbon\Carbon::parse($endDateStr . ' ' . $event->end_time);
                        if ($eventEnd->lt($eventStart)) {
                            $eventEnd->addDay();
                        }
                    } else {
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
                        $startDisplay .= ' <br><span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200 dark:border-sky-800">Lanjutan Kemarin</span>';
                    }

                    $endDisplay = '-';
                    if ($event->end_time) {
                        $endDisplay = $clampedEnd->format('H:i:s');
                        if ($eventEnd->gt($rangeEndFinal)) {
                            $endDisplay .= ' <br><span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border border-sky-200 dark:border-sky-800">Berlanjut Besok</span>';
                        }
                    }
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-400 dark:text-slate-500">{{ $no++ }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $statusColor === 'success' ? 'bg-emerald-500' : ($statusColor === 'danger' ? 'bg-rose-500' : ($statusColor === 'warning' ? 'bg-amber-500' : 'bg-slate-400')) }}"></span>
                                <span class="font-bold text-xs text-slate-900 dark:text-white">
                                    {{ $event->mesin->nama_mesin ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($event->downtime_type == 'aktif' || $event->downtime_type == 'running')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span>Running</span>
                            </span>
                            @elseif($event->downtime_type == 'maintenance' || $event->downtime_type == 'breakdown')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                                <i class="fas fa-wrench text-[10px]"></i>
                                <span>Breakdown</span>
                            </span>
                            @elseif($event->downtime_type == 'offline' || $event->downtime_type == 'tidak aktif')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <i class="fas fa-power-off text-[10px]"></i>
                                <span>Off</span>
                            </span>
                            @elseif(str_contains(strtolower($event->title), 'call:'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $event->status === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/70 dark:text-emerald-300' : ($event->status === 'escalated' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/70 dark:text-amber-300' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/70 dark:text-rose-300') }} border">
                                <i class="fas fa-bullhorn text-[10px]"></i>
                                <span>{{ $statusLabel }}</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                                <i class="fas fa-pause text-[10px]"></i>
                                <span>Stand By</span>
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-col">
                                @if($event->user)
                                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ $event->user->name ?? $event->user->nama }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium capitalize">{{ strtolower($event->user->role ?? 'User') }}</span>
                                @else
                                <span class="font-bold text-xs text-slate-500 dark:text-slate-400">System / Auto</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Monitoring</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center font-mono text-xs text-slate-600 dark:text-slate-400">{!! $startDisplay !!}</td>
                        <td class="text-center font-mono text-xs text-slate-600 dark:text-slate-400">{!! $endDisplay !!}</td>
                        <td class="text-center font-mono text-xs font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                            {!! $duration !!}
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400 line-clamp-1" title="{{ $event->description ?? $event->keterangan ?? '-' }}">
                                {{ $event->description ?? $event->keterangan ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if(str_contains(strtolower($event->title), 'call:') && ($event->status === 'pending' || $event->status === 'escalated') && in_array(Auth::user()->role, ['leader', 'foreman', 'kabag', 'super_admin']))
                                <button class="btn-handle-call inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-all cursor-pointer" 
                                        data-id="{{ $event->id }}" 
                                        data-machine="{{ $event->mesin->nama_mesin }}"
                                        data-title="{{ $event->title }}">
                                    <i class="fas fa-search-plus text-[10px]"></i> Check
                                </button>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-8">
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                <p class="text-xs font-medium m-0">Tidak ada data downtime untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" 
    class="fixed bottom-6 right-6 w-11 h-11 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-lg shadow-indigo-600/30 flex items-center justify-center transition-all cursor-pointer z-50 hidden"
    title="Kembali ke atas">
    <i class="fas fa-arrow-up text-sm"></i>
</button>

<!-- Machine Detail Modal -->
<div class="modal fade" id="machineDetailModal" tabindex="-1" role="dialog" aria-labelledby="machineDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl bg-white dark:bg-slate-900 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0" id="machineDetailModalLabel">
                            Detail Aktivitas <span id="modalMachineName" class="font-black text-indigo-600 dark:text-indigo-400 ml-1"></span>
                        </h5>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Rincian status dan durasi mesin terpilih</p>
                    </div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer transition-colors" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <input type="hidden" id="modal-active-mesin-id">
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/30 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-0.5">Running</div>
                        <div class="text-base font-black font-mono text-emerald-700 dark:text-emerald-300" id="modalRuntime">0j 0m</div>
                    </div>
                    <div class="p-3 bg-rose-50/60 dark:bg-rose-950/30 rounded-xl border border-rose-200/60 dark:border-rose-800/40">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-0.5">Breakdown</div>
                        <div class="text-base font-black font-mono text-rose-700 dark:text-rose-300" id="modalMaintenance">0j 0m</div>
                    </div>
                    <div class="p-3 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl border border-amber-200/60 dark:border-amber-800/40">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-0.5">Stand By</div>
                        <div class="text-base font-black font-mono text-amber-700 dark:text-amber-300" id="modalStandby">0j 0m</div>
                    </div>
                    <div class="p-3 bg-slate-100/60 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-0.5">OFF / Stop</div>
                        <div class="text-base font-black font-mono text-slate-700 dark:text-slate-300" id="modalOffline">0j 0m</div>
                    </div>
                </div>

                <div class="border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="fas fa-list text-indigo-500 mr-1.5"></i> Log Aktivitas Terpilih
                        </span>
                    </div>
                    <div class="overflow-y-auto max-h-[300px]">
                        <table class="w-full text-xs text-left" id="modalLogTable">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">Waktu Start</th>
                                    <th class="px-3 py-2">Waktu Selesai</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">User</th>
                                    <th class="px-3 py-2">Durasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <!-- Filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-800/50">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" data-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer" id="btnFilterTable">
                    <i class="fas fa-search text-[10px]"></i>
                    <span>Lihat di Tabel Utama</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
$currentUserName = auth()->user()?->nama ?? 'Guest';
$currentUserRole = auth()->user()?->role ?? 'Viewer';
@endphp
<div id="history-log-data" data-logs="{{ base64_encode(json_encode($historyLog)) }}" style="display: none;"></div>

<input type="hidden" id="current-user-name" value="{{ $currentUserName }}">
<input type="hidden" id="current-user-role" value="{{ $currentUserRole }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="{{ asset('js/history-page.js') }}?v={{ filemtime(public_path('js/history-page.js')) }}"></script>
@endpush