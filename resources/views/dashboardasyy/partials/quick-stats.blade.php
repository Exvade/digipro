<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clock mr-2"></i>
            <span class="d-none d-sm-inline">Update Terakhir</span>
            <span class="d-sm-none">Update</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="text-center">
            <div class="mb-2">
                <i class="fas fa-calendar-alt fa-2x text-info d-none d-md-block"></i>
                <i class="fas fa-calendar-alt fa-lg text-info d-md-none"></i>
            </div>
            <h5 class="d-none d-md-block">{{ now()->format('d M Y') }}</h5>
            <h6 class="d-md-none">{{ now()->format('d M Y') }}</h6>
            <p class="text-muted text-mobile-sm">{{ now()->format('H:i') }} WIB</p>
            <small class="text-muted text-mobile-xs">Last update: {{ now()->format('d M Y H:i') }}</small>
        </div>
    </div>
</div> 