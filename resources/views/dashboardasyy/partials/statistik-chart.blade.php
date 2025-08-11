<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-2"></i>
            <span class="d-none d-sm-inline">Statistik Meja</span>
            <span class="d-sm-none">Statistik</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="text-center mb-4 d-none d-md-block">
            <div class="mb-3">
                <i class="fas fa-industry fa-3x text-primary"></i>
            </div>
            <h4>Total Meja</h4>
            <h2 class="text-primary">{{ $totalMejas }}</h2>
        </div>
        <div class="text-center mb-4 d-md-none">
            <div class="mb-2">
                <i class="fas fa-industry fa-2x text-primary"></i>
            </div>
            <h5>Total Meja</h5>
            <h3 class="text-primary">{{ $totalMejas }}</h3>
        </div>
        <div class="chart-container mb-4">
            <canvas id="pieChartMeja"></canvas>
        </div>
        <div class="mt-4">
            <h6 class="font-weight-bold mb-3 text-mobile-sm">Status Meja:</h6>
            <div class="d-flex align-items-center mb-2">
                <div class="bg-success mr-2" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                <span class="text-mobile-sm">Aktif ({{ $countAktif }})</span>
            </div>
            <div class="d-flex align-items-center mb-2">
                <div class="bg-warning mr-2" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                <span class="text-mobile-sm">Proses ({{ $countMaintenance }})</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="bg-danger mr-2" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                <span class="text-mobile-sm">Tidak Aktif ({{ $countTidakAktif }})</span>
            </div>
        </div>
    </div>
</div> 