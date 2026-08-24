<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Mesin</label>
            <p class="form-control-plaintext">{{ $checksheet->mesin->nama_mesin ?? '-' }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Part</label>
            <p class="form-control-plaintext">{{ $checksheet->part->nama_part ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">User</label>
            <p class="form-control-plaintext">{{ $checksheet->user->nama ?? '-' }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Grup</label>
            <p class="form-control-plaintext">
                {{ $checksheet->user->grup->nama_grup ?? '-' }}
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Meja</label>
            <p class="form-control-plaintext">{{ $checksheet->meja->nama_meja ?? '-' }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Shift</label>
            <p class="form-control-plaintext">
                <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Status Approval</label>
            <p class="form-control-plaintext">
                @if($checksheet->approval_status == 'pending')
                    <span class="badge badge-warning">
                        <i class="fas fa-clock"></i>Pending
                    </span>
                @elseif($checksheet->approval_status == 'approved')
                    <span class="badge badge-success">
                        <i class="fas fa-check"></i>Approved
                    </span>
                @else
                    <span class="badge badge-danger">
                        <i class="fas fa-times"></i>Rejected
                    </span>
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Tanggal Dibuat</label>
            <p class="form-control-plaintext">{{ $checksheet->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>

<!-- Approval Information (if not pending) -->
@if($checksheet->approval_status != 'pending')
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Disetujui Oleh</label>
            <p class="form-control-plaintext">{{ $checksheet->approver->nama ?? '-' }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Tanggal Approval</label>
            <p class="form-control-plaintext">{{ $checksheet->approved_at ? $checksheet->approved_at->format('d/m/Y H:i') : '-' }}</p>
        </div>
    </div>
</div>

@if($checksheet->approval_status == 'rejected' && $checksheet->rejection_reason)
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Alasan Penolakan:</strong> {{ $checksheet->rejection_reason }}
        </div>
    </div>
</div>
@endif
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="font-weight-bold">Terakhir Diupdate</label>
            <p class="form-control-plaintext">{{ $checksheet->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>

<hr class="my-4">
<h5 class="font-weight-bold text-primary mb-3">
    <i class="fas fa-cogs mr-2"></i>Parameter Kondisi Mesin (CBM)
</h5>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label class="font-weight-bold"><i class="fas fa-thermometer-half text-danger mr-1"></i>Suhu Barrel/Heater</label>
            <p class="form-control-plaintext">
                @if($checksheet->suhu_mesin)
                    {{ $checksheet->suhu_mesin }} °C
                    @if($checksheet->suhu_mesin > 280)
                        <span class="badge badge-danger ml-2">Abnormal</span>
                    @else
                        <span class="badge badge-success ml-2">Normal</span>
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="font-weight-bold"><i class="fas fa-tachometer-alt text-primary mr-1"></i>Tekanan Hidrolik</label>
            <p class="form-control-plaintext">
                @if($checksheet->tekanan_mesin)
                    {{ $checksheet->tekanan_mesin }} Bar
                    @if($checksheet->tekanan_mesin > 150)
                        <span class="badge badge-danger ml-2">Abnormal</span>
                    @else
                        <span class="badge badge-success ml-2">Normal</span>
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="font-weight-bold"><i class="fas fa-wave-square text-warning mr-1"></i>Getaran Motor</label>
            <p class="form-control-plaintext">
                @if($checksheet->getaran_mesin)
                    {{ $checksheet->getaran_mesin }} mm/s
                    @if($checksheet->getaran_mesin > 25)
                        <span class="badge badge-danger ml-2">Abnormal</span>
                    @else
                        <span class="badge badge-success ml-2">Normal</span>
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
    </div>
</div>

@if($checksheet->photos->count() > 0)
<hr class="my-4">
<div class="section-photos">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="font-weight-bold text-primary mb-0">
            <i class="fas fa-images mr-2"></i>Foto Checksheet
        </h5>
        <span class="badge badge-secondary">{{ $checksheet->photos->count() }} foto</span>
    </div>
    
    <div class="photo-grid">
        <!-- First Row - 3 Photos -->
        <div class="photo-row first-row">
            @foreach($checksheet->photos->take(3) as $index => $photo)
            <div class="photo-item">
                <div class="card photo-card h-100 shadow-sm border-0">
                    <div class="photo-container position-relative">
                        <img src="{{ asset('storage/' . $photo->file_path) }}" 
                             class="card-img-top photo-img" 
                             alt="{{ $photo->type_display_name }}"
                             data-toggle="modal" 
                             data-target="#photoModal{{ $index }}"
                             style="height: 200px; object-fit: cover; cursor: pointer;">
                        <div class="photo-overlay">
                            <i class="fas fa-search-plus text-white"></i>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="photo-info">
                            <h6 class="card-title text-primary mb-1">{{ $photo->type_display_name }}</h6>
                            @if($photo->description)
                            <p class="card-text text-muted small mb-2">{{ $photo->description }}</p>
                            @endif
                            <div class="photo-meta">
                                <small class="text-muted">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $photo->created_at ? $photo->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Second Row - 2 Photos -->
        @if($checksheet->photos->count() > 3)
        <div class="photo-row second-row">
            @foreach($checksheet->photos->slice(3, 2) as $index => $photo)
            <div class="photo-item">
                <div class="card photo-card h-100 shadow-sm border-0">
                    <div class="photo-container position-relative">
                        <img src="{{ asset('storage/' . $photo->file_path) }}" 
                             class="card-img-top photo-img" 
                             alt="{{ $photo->type_display_name }}"
                             data-toggle="modal" 
                             data-target="#photoModal{{ $index + 3 }}"
                             style="height: 200px; object-fit: cover; cursor: pointer;">
                        <div class="photo-overlay">
                            <i class="fas fa-search-plus text-white"></i>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="photo-info">
                            <h6 class="card-title text-primary mb-1">{{ $photo->type_display_name }}</h6>
                            @if($photo->description)
                            <p class="card-text text-muted small mb-2">{{ $photo->description }}</p>
                            @endif
                            <div class="photo-meta">
                                <small class="text-muted">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $photo->created_at ? $photo->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Modals for all photos -->
@foreach($checksheet->photos as $index => $photo)
<div class="modal fade" id="photoModal{{ $index }}" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel{{ $index }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="photoModalLabel{{ $index }}">
                    <i class="fas fa-image mr-2"></i>{{ $photo->type_display_name }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <img src="{{ asset('storage/' . $photo->file_path) }}" 
                     class="img-fluid w-100" 
                     alt="{{ $photo->type_display_name }}">
            </div>
            <div class="modal-footer">
                @if($photo->description)
                <div class="text-left flex-grow-1">
                    <strong>Deskripsi:</strong>
                    <p class="mb-0">{{ $photo->description }}</p>
                </div>
                @endif
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Include external CSS file -->
<link rel="stylesheet" href="{{ asset('css/approval-photos.css') }}">
@else
<!-- Empty State -->
<div class="text-center py-5">
    <i class="fas fa-camera fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Belum ada foto</h5>
    <p class="text-muted">Foto checksheet akan ditampilkan di sini setelah diambil.</p>
</div>
@endif 