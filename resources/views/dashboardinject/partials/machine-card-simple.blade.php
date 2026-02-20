@php
$statusClass = 'running';
$statusText = 'Running';
$statusIcon = 'fa-check-circle';

if ($mesin->status === 'maintenance') {
$statusClass = 'maintenance';
$statusText = 'BREAKDOWN' . ($mesin->stop_reason ? ' (' . $mesin->stop_reason . ')' : '');
$statusIcon = 'fa-tools';
} elseif ($mesin->status === 'offline') {
$statusClass = 'tidak-aktif';
$statusText = 'OFF' . ($mesin->stop_reason ? ' (' . $mesin->stop_reason . ')' : '');
$statusIcon = 'fa-power-off';
} elseif ($mesin->status === 'stop') {
$statusClass = 'stop';
$statusText = 'STAND BY' . ($mesin->stop_reason ? ' (' . $mesin->stop_reason . ')' : '');
$statusIcon = 'fa-stop-circle';

// Keep icons for specific common reasons if they exist in the new list
if (strpos($mesin->stop_reason, 'Setup') !== false) {
$statusIcon = 'fa-wrench';
} elseif (strpos($mesin->stop_reason, 'Set Down') !== false || strpos($mesin->stop_reason, 'Setdown') !== false) {
$statusIcon = 'fa-cog';
} elseif (strpos($mesin->stop_reason, 'Material Habis') !== false) {
$statusIcon = 'fa-box-open';
} elseif (strpos($mesin->stop_reason, 'Packing Habis') !== false) {
$statusIcon = 'fa-boxes';
}
}

$prodData = $machineProductionData[$mesin->id] ?? null;
$progressPercentage = $prodData['progress_percentage'] ?? 0;

// Split machine name for better layout
// Assuming format "Mesin 1" -> "MC 1"
$machineId = str_replace('Mesin', 'MC', $mesin->nama_mesin);
$machineBrand = $mesin->brand_mesin . ' ' . $mesin->tonase_mesin . 'T';
@endphp

<div class="col-custom-5 mb-3">
    {{-- Simple Card --}}
    <div class="card machine-card-simple {{ $statusClass }}"
        data-mesin-id="{{ $mesin->id }}"
        data-mesin-nama="{{ $mesin->nama_mesin }}"
        data-mesin-brand="{{ $mesin->brand_mesin }}"
        data-mesin-tonase="{{ $mesin->tonase_mesin }}"
        data-mesin-status="{{ $mesin->status }}"
        data-stop-reason="{{ $mesin->stop_reason }}"
        data-mesin-history="{{ json_encode($prodData['history'] ?? []) }}"
        data-status-since="{{ $mesin->status_since ? $mesin->status_since->toIso8601String() : '' }}"
        style="cursor: pointer; position: relative;">

        {{-- Edit Button untuk Leader/Foreman --}}
        @auth
        @if(auth()->user()->role === 'leader' || auth()->user()->role === 'foreman')
        <button type="button"
            class="btn btn-sm btn-edit-machine"
            data-mesin-id="{{ $mesin->id }}"
            title="Edit Mesin">
            <i class="fas fa-edit"></i>
        </button>
        @endif
        @endauth

        <div class="card-body-simple text-center">
            <span class="machine-name-simple">{{ $machineId }}</span>
            <span class="machine-details-simple">({{ $machineBrand }})</span>

            {{-- Part Name Info (Hidden as requested) --}}
            {{--
            <div class="{{ $mesin->status !== 'running' ? 'opacity-50' : '' }}">
            <span class="machine-part-simple text-truncate" title="{{ $prodData['part_name'] ?? 'Tidak ada part' }}">
                <i class="fas fa-cube mr-1"></i> {{ $prodData['part_name'] ?? '-' }}
            </span>
        </div>
        --}}

        {{-- Quantity Info per Shift (Hidden as requested) --}}
        {{--
            <div class="machine-qty-info mt-1 mb-1">
                <span class="badge badge-pill badge-light shadow-sm" style="font-size: 0.65rem;" title="Target per Shift">
                    <i class="fas fa-bullseye text-primary mr-1"></i>
                    {{ $prodData['actual_current_shift'] ?? 0 }} / {{ $prodData['target_current_shift'] ?? 0 }}
        </span>
        @if(isset($prodData['target_hari_ini']) && $prodData['target_hari_ini'] > 0)
        <div class="small text-muted mt-1" style="font-size: 0.6rem;">
            Total Target: <strong>{{ number_format($prodData['target_hari_ini']) }}</strong>
        </div>
        @endif
    </div>
    --}}

    <div class="machine-status-text machine-status-display">
        <i class="fas {{ $statusIcon }} mr-1"></i> {{ $statusText }}
    </div>


    {{-- Progress Bar (Hidden as requested) --}}
    {{--
            @if($prodData && $prodData['has_planning'] && $mesin->status === 'running')
            <div class="progress-bar-top mt-2" title="Progress: {{ $progressPercentage }}%">
    <div class="progress-bar-fill" style="--progress-width: {{ $progressPercentage }}%;"></div>
    <span class="progress-text">{{ $progressPercentage }}%</span>
</div>
@endif
--}}
</div>
</div>
</div>