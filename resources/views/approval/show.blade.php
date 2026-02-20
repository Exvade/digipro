{{-- Tentukan tipe operator berdasarkan mesin_id atau meja_id --}}
@php
    $isInject = !empty($checksheet->mesin_id);
    $isAssy = !empty($checksheet->meja_id);
@endphp

<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle mr-2"></i>Informasi Checksheet
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-user mr-1"></i>Operator
                    </label>
                    <p class="form-control-plaintext">{{ $checksheet->user->nama ?? '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-users mr-1"></i>Grup
                    </label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-secondary">{{ $checksheet->user->grup->nama_grup ?? '-' }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-box mr-1"></i>Part
                    </label>
                    <p class="form-control-plaintext">{{ $checksheet->part->nama_part ?? '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-clock mr-1"></i>Shift
                    </label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Tampilkan Mesin untuk Operator Inject --}}
        @if($isInject)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-cog mr-1"></i>Mesin
                    </label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-success">{{ $checksheet->mesin->nama_mesin ?? '-' }}</span>
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Tampilkan Meja untuk Operator Assy --}}
        @if($isAssy)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-table mr-1"></i>Meja
                    </label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-success">{{ $checksheet->meja->nama_meja ?? '-' }}</span>
                    </p>
                </div>
            </div>
        </div>
        @endif

        <hr class="my-3">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i>Status
                    </label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-warning">
                            <i class="fas fa-clock mr-1"></i>Pending
                        </span>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-primary">
                        <i class="fas fa-calendar mr-1"></i>Tanggal Dibuat
                    </label>
                    <p class="form-control-plaintext">{{ $checksheet->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
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
                        <img src="{{ url('storage/' . ltrim(str_replace(['public/','storage/'], '', $photo->file_path), '/')) }}" 
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
                        <img src="{{ url('storage/' . ltrim(str_replace(['public/','storage/'], '', $photo->file_path), '/')) }}" 
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
                <img src="{{ url('storage/' . ltrim(str_replace(['public/','storage/'], '', $photo->file_path), '/')) }}" 
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
@endif

<div class="row mt-3">
    <div class="col-12">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success mx-1"  onclick="approveChecksheet({{ $checksheet->id }})">
                <i class="fas fa-check mr-1"></i>Setujui
            </button>
            <button type="button" class="btn btn-danger mx-1"  onclick="rejectChecksheet({{ $checksheet->id }})">
                <i class="fas fa-times mr-1"></i>Tolak
            </button>
        </div>
    </div>
</div> 