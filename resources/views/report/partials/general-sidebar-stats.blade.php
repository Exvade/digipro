<!-- General statistics for other roles -->
<div class="card sidebar-stats">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-2"></i>
            Statistik
        </h3>
    </div>
    <div class="card-body">
        <!-- Shift Statistics -->
        <div class="stat-section">
            <h6>Statistik per Shift</h6>
            @foreach($shiftStats as $stat)
            <div class="stat-item-sidebar">
                <span class="text-capitalize">{{ $stat->shift }}</span>
                <span class="badge badge-primary">{{ $stat->total }}</span>
            </div>
            @endforeach
        </div>
        
        <hr>
        
        <!-- Group Statistics -->
        <div class="stat-section">
            <h6>Top 5 Grup</h6>
            @foreach($groupStats as $stat)
            <div class="stat-item-sidebar">
                <span class="text-truncate" style="max-width: 150px;">
                    {{ $stat->nama_grup ?? 'Unknown' }}
                </span>
                <span class="badge badge-info">{{ $stat->total }}</span>
            </div>
            @endforeach
        </div>
        
        <hr>
        
        <!-- Machine/Meja Statistics -->
        <div class="stat-section">
            <h6>Top 5 Mesin/Meja</h6>
            @foreach($machineStats as $stat)
            <div class="stat-item-sidebar">
                <span class="text-truncate" style="max-width: 150px;">
                    @if($stat->mesin)
                        {{ $stat->mesin->nama_mesin ?? 'Unknown' }}
                    @elseif($stat->meja)
                        {{ $stat->meja->nama_meja ?? 'Unknown' }}
                    @else
                        Unknown
                    @endif
                </span>
                <span class="badge badge-info">{{ $stat->total }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div> 