<!-- Statistics for Assy Department -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-2"></i>
            Statistik (Produksi Assy)
        </h3>
    </div>
    <div class="card-body">
        <!-- Shift Statistics -->
        <h6 class="font-weight-bold">Statistik per Shift</h6>
        @if(isset($shiftStats) && count($shiftStats) > 0)
            @foreach($shiftStats as $stat)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-capitalize">{{ is_array($stat) ? ($stat['shift'] ?? '-') : ($stat->shift ?? '-') }}</span>
                <span class="badge badge-primary">{{ is_array($stat) ? ($stat['total'] ?? 0) : ($stat->total ?? 0) }}</span>
            </div>
            @endforeach
        @else
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Tidak ada data</span>
            </div>
        @endif
        
        <hr>
        
        <!-- Meja Statistics -->
        <h6 class="font-weight-bold">Top 5 Meja</h6>
        @foreach($machineStats as $stat)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-truncate" style="max-width: 150px;">{{ $stat->meja->nama_meja ?? 'Unknown' }}</span>
            <span class="badge badge-info">{{ $stat->total }}</span>
        </div>
        @endforeach
    </div>
</div> 