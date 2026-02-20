<form id="updateChecksheetForm" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" id="checksheet_id" value="{{ $checksheet->id }}">
    
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mesin_id">Mesin <span class="text-danger">*</span></label>
                    <select class="form-control" id="mesin_id" name="mesin_id" required>
                        <option value="">Pilih Mesin</option>
                        @foreach($mesins as $mesin)
                        <option value="{{ $mesin->id }}" {{ $checksheet->mesin_id == $mesin->id ? 'selected' : '' }}>
                            {{ $mesin->nama_mesin }} - {{ $mesin->brand_mesin }}
                        </option>
                        @endforeach
                    </select>
                    <small id="mesinHelp" class="form-text text-muted" style="display:none;">Hanya dapat diisi untuk bagian Inject</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="part_id">Part <span class="text-danger">*</span></label>
                    <select class="form-control" id="part_id" name="part_id" required>
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}" {{ $checksheet->part_id == $part->id ? 'selected' : '' }}>
                            {{ $part->no_parts }} - {{ $part->nama_part }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="user_id">User <span class="text-danger">*</span></label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="">Pilih User</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $checksheet->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->nama }} ({{ $user->username }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="grup_id">Grup</label>
                    <select class="form-control" id="grup_id" name="grup_id">
                        <option value="">Pilih Grup (Opsional)</option>
                        @foreach($grups as $grup)
                        <option value="{{ $grup->id }}" {{ $checksheet->grup_id == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="meja_id">Meja (Opsional)</label>
                    <select class="form-control" id="meja_id" name="meja_id">
                        <option value="">Pilih Meja</option>
                        @foreach($mejas as $meja)
                        <option value="{{ $meja->id_meja }}" {{ $checksheet->meja_id == $meja->id_meja ? 'selected' : '' }}>
                            {{ $meja->nama_meja }}
                        </option>
                        @endforeach
                    </select>
                    <small id="mejaHelp" class="form-text text-muted" style="display:none;">Hanya dapat diisi untuk bagian Assy</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="shift_display">Shift</label>
                    <input type="text" class="form-control" id="shift_display" 
                        value="{{ $checksheet->shift_display ?? '-' }}" readonly 
                        style="background-color: #e9ecef;">
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>Shift otomatis dari jadwal kerja
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status Approval</label>
                    <div class="form-control-plaintext">
                        @if($checksheet->approval_status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($checksheet->approval_status == 'approved')
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Bagian Foto Checksheet -->
        <h6 class="font-weight-bold mb-3">Foto Checksheet</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold">Foto APD</label>
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="openCamera('apd')">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <div id="preview_apd" style="display: none;">
                        <img id="apd_preview" src="{{ old('foto_apd') ?: '' }}" alt="Preview APD" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                    </div>
                </div>
                <input type="hidden" id="foto_apd" name="foto_apd" value="{{ old('foto_apd') ?: '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold">Foto Instruksi Kerja</label>
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="openCamera('instruksi_kerja')">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <div id="preview_instruksi_kerja" style="display: none;">
                        <img id="instruksi_kerja_preview" src="{{ old('foto_instruksi_kerja') ?: '' }}" alt="Preview Instruksi Kerja" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                    </div>
                </div>
                <input type="hidden" id="foto_instruksi_kerja" name="foto_instruksi_kerja" value="{{ old('foto_instruksi_kerja') ?: '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold">Foto Q-Point</label>
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="openCamera('qpoint')">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <div id="preview_qpoint" style="display: none;">
                        <img id="qpoint_preview" src="{{ old('foto_qpoint') ?: '' }}" alt="Preview Q-Point" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                    </div>
                </div>
                <input type="hidden" id="foto_qpoint" name="foto_qpoint" value="{{ old('foto_qpoint') ?: '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold">Foto Limit Sampel</label>
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="openCamera('limit_sampel')">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <div id="preview_limit_sampel" style="display: none;">
                        <img id="limit_sampel_preview" src="{{ old('foto_limit_sampel') ?: '' }}" alt="Preview Limit Sampel" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                    </div>
                </div>
                <input type="hidden" id="foto_limit_sampel" name="foto_limit_sampel" value="{{ old('foto_limit_sampel') ?: '' }}">
            </div>
        </div>

        <!-- AMTPM Section -->
        <hr>
        <h6 class="font-weight-bold mb-3">Standar AMTPM</h6>
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-group">
                    <label>1. Apakah terdapat Standar AMTPM?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="amptm_standar" id="amptm_standar_ya" value="ya" {{ $checksheet->amptm_standar == 'ya' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="amptm_standar_ya">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="amptm_standar" id="amptm_standar_tidak" value="tidak" {{ $checksheet->amptm_standar == 'tidak' ? 'checked' : '' }}>
                        <label class="form-check-label" for="amptm_standar_tidak">Tidak</label>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#amptmExampleModal">
                            <i class="fas fa-eye mr-1"></i>Lihat Foto Standar AMTPM
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="font-weight-bold">Foto Standar AMTPM</label>
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="openCamera('amptm')">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <div id="preview_amptm" style="display: none;">
                        <img id="amptm_preview" src="{{ old('foto_amptm') ?: '' }}" alt="Preview Standar AMTPM" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                    </div>
                </div>
                <input type="hidden" id="foto_amptm" name="foto_amptm" value="{{ old('foto_amptm') ?: '' }}">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Update Checksheet
        </button>
    </div>
</form>

<!-- AMTPM Example Modal -->
<div class="modal fade" id="amptmExampleModal" tabindex="-1" role="dialog" aria-labelledby="amptmExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amptmExampleModalLabel">
                    <i class="fas fa-clipboard-list mr-2"></i>Foto Standar AMTPM
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @php
                    $amptmPhotos = $checksheet->photos->where('type', 'amptm');
                @endphp
                
                @if($amptmPhotos->count() > 0)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Foto Standar AMTPM tersimpan:</strong> Berikut adalah foto Standar AMTPM yang telah diambil untuk checksheet ini.
                    </div>
                    <div class="row">
                        @foreach($amptmPhotos as $photo)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                     class="card-img-top" 
                                     alt="Foto Standar AMTPM" 
                                     style="height: 300px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $photo->type_display_name }}</h6>
                                    @if($photo->description)
                                    <p class="card-text text-muted">{{ $photo->description }}</p>
                                    @endif
                                    <small class="text-muted">Diambil pada: {{ $photo->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Tidak ada foto Standar AMTPM:</strong> Belum ada foto Standar AMTPM yang diambil untuk checksheet ini.
                    </div>
                    <div class="text-center">
                        <p class="text-muted">Foto Standar AMTPM akan ditampilkan di sini setelah diambil menggunakan kamera.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kamera -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-labelledby="cameraModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="cameraModalLabel"><i class="fas fa-camera mr-2"></i>Ambil Foto</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="font-size:1.5rem;">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <video id="cameraStream" style="width:100%;max-width:350px;border-radius:8px;" autoplay></video>
        <canvas id="cameraCanvas" style="display:none;"></canvas>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="takePhotoBtn"><i class="fas fa-camera"></i> Ambil Foto</button>
      </div>
    </div>
  </div>
</div>

<!-- Script Kamera langsung di bawah modal agar selalu aktif -->
<script>
let currentPhotoType = null;
let stream = null;

function openCamera(type) {
    currentPhotoType = type;
    $('#cameraModal').modal('show');
    const video = document.getElementById('cameraStream');
    // Stop stream jika sudah ada
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(s) {
            stream = s;
            video.srcObject = stream;
        })
        .catch(function(err) {
            alert('Tidak dapat mengakses kamera: ' + err.message);
        });
}

// Stop kamera saat modal ditutup
$('#cameraModal').on('hidden.bs.modal', function () {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
});

// Ambil foto
$('#takePhotoBtn').on('click', function() {
    const video = document.getElementById('cameraStream');
    const canvas = document.getElementById('cameraCanvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg');
    // Set preview
    $(`#${currentPhotoType}_preview`).attr('src', dataUrl);
    $(`#preview_${currentPhotoType}`).show();
    // Set hidden input value
    $(`#foto_${currentPhotoType}`).val(dataUrl);
    $('#cameraModal').modal('hide');
});

// Logika disable field mesin/meja sesuai bagian user
$(document).ready(function() {
    // Ambil bagian user dari data checksheet
    var userBagian = @json($checksheet->user->bagian ?? null);
    if (userBagian === 'assy') {
        $('#mesin_id').prop('disabled', true);
        $('#mesinHelp').show();
        $('#meja_id').prop('disabled', false);
        $('#mejaHelp').hide();
    } else if (userBagian === 'inject') {
        $('#meja_id').prop('disabled', true);
        $('#mejaHelp').show();
        $('#mesin_id').prop('disabled', false);
        $('#mesinHelp').hide();
    } else {
        // Jika bukan assy/inject, keduanya bisa diisi
        $('#mesin_id').prop('disabled', false);
        $('#meja_id').prop('disabled', false);
        $('#mesinHelp').hide();
        $('#mejaHelp').hide();
    }
});

// Tampilkan preview jika sudah ada value pada input hidden (misal dari old value)
$(document).ready(function() {
    const fotoFields = ['apd', 'instruksi_kerja', 'qpoint', 'limit_sampel', 'amptm'];
    fotoFields.forEach(function(type) {
        const val = $(`#foto_${type}`).val();
        if (val && val.startsWith('data:image')) {
            $(`#${type}_preview`).attr('src', val);
            $(`#preview_${type}`).show();
        }
    });
});
</script>

<link rel="stylesheet" href="{{ asset('css/checksheet-update.css') }}">
<script src="{{ asset('js/checksheet-update.js') }}"></script>
