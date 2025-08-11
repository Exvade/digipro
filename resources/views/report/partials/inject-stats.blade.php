<!-- Inject Department Statistics -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Statistik Mesin (Produksi Inject)
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Mesin</span>
                                <span class="info-box-number">{{ $departmentSpecificData['stats']['total'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Aktif</span>
                                <span class="info-box-number">{{ $departmentSpecificData['stats']['aktif'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Maintenance</span>
                                <span class="info-box-number">{{ $departmentSpecificData['stats']['maintenance'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tidak Aktif</span>
                                <span class="info-box-number">{{ $departmentSpecificData['stats']['tidak_aktif'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 