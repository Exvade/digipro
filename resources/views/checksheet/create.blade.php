<form id="createChecksheetForm" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="user_id">User <span class="text-danger">*</span></label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="">Pilih User</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}" data-bagian="{{ $user->bagian }}" data-grup-id="{{ $user->grup_id }}">
                            {{ $user->nama }} ({{ $user->username }}) - {{ ucfirst($user->role) }} - {{ ucfirst($user->bagian ?? 'N/A') }}
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
                        <option value="{{ $grup->id }}">
                            {{ $grup->nama_grup }}
                        </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Grup akan otomatis terisi sesuai user yang dipilih</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="shift">Shift <span class="text-danger">*</span></label>
                    <select class="form-control" id="shift" name="shift" required>
                        <option value="">Pilih Shift</option>
                        <option value="shift_1">Pagi (07:00 - 15:00)</option>
                        <option value="shift_2">Siang (15:00 - 23:00)</option>
                        <option value="shift_3">Malam (23:00 - 07:00)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Mesin Section - Only for Inject -->
        <div class="row" id="mesin_section" style="display: none;">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mesin_id">Mesin <span class="text-danger">*</span></label>
                    <select class="form-control" id="mesin_id" name="mesin_id">
                        <option value="">Pilih Mesin</option>
                        @foreach($mesins as $mesin)
                        <option value="{{ $mesin->id }}">{{ $mesin->nama_mesin }} - {{ $mesin->brand_mesin }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="part_id">Part <span class="text-danger">*</span></label>
                    <select class="form-control" id="part_id" name="part_id">
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->no_parts }} - {{ $part->nama_part }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Meja Section - Only for Assy -->
        <div class="row" id="meja_section" style="display: none;">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="meja_id">Meja <span class="text-danger">*</span></label>
                    <select class="form-control" id="meja_id" name="meja_id">
                        <option value="">Pilih Meja</option>
                        @foreach($mejas as $meja)
                        <option value="{{ $meja->id_meja }}">{{ $meja->nama_meja }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="part_id_assy">Part <span class="text-danger">*</span></label>
                    <select class="form-control" id="part_id_assy" name="part_id_assy">
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->no_parts }} - {{ $part->nama_part }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <hr>

        <h6 class="font-weight-bold">Foto Checksheet</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold mb-2">
                            <i class="fas fa-hard-hat mr-1"></i>Foto APD
                        </label>
                        <div class="camera-container">
                            <video id="camera-apd" class="camera-preview" autoplay playsinline style="display: none;"></video>
                            <canvas id="canvas-apd" class="camera-canvas" style="display: none;"></canvas>
                            <div id="preview-apd" class="camera-preview-container" style="display: none;">
                                <img id="apd_preview_img" src="" alt="Preview APD" class="img-fluid rounded">
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="apd">
                                    <i class="fas fa-camera mr-1"></i>Buka Kamera
                                </button>
                                <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="apd" style="display: none;">
                                    <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="apd" style="display: none;">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                            <input type="file" id="foto_apd" name="foto_apd" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold mb-2">
                            <i class="fas fa-file-alt mr-1"></i>Foto Instruksi Kerja
                        </label>
                        <div class="camera-container">
                            <video id="camera-instruksi_kerja" class="camera-preview" autoplay playsinline style="display: none;"></video>
                            <canvas id="canvas-instruksi_kerja" class="camera-canvas" style="display: none;"></canvas>
                            <div id="preview-instruksi_kerja" class="camera-preview-container" style="display: none;">
                                <img id="instruksi_kerja_preview_img" src="" alt="Preview Instruksi Kerja" class="img-fluid rounded">
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="instruksi_kerja">
                                    <i class="fas fa-camera mr-1"></i>Buka Kamera
                                </button>
                                <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="instruksi_kerja" style="display: none;">
                                    <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="instruksi_kerja" style="display: none;">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                            <input type="file" id="foto_instruksi_kerja" name="foto_instruksi_kerja" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold mb-2">
                            <i class="fas fa-award mr-1"></i>Foto Q-Point
                        </label>
                        <div class="camera-container">
                            <video id="camera-qpoint" class="camera-preview" autoplay playsinline style="display: none;"></video>
                            <canvas id="canvas-qpoint" class="camera-canvas" style="display: none;"></canvas>
                            <div id="preview-qpoint" class="camera-preview-container" style="display: none;">
                                <img id="qpoint_preview_img" src="" alt="Preview Q-Point" class="img-fluid rounded">
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="qpoint">
                                    <i class="fas fa-camera mr-1"></i>Buka Kamera
                                </button>
                                <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="qpoint" style="display: none;">
                                    <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="qpoint" style="display: none;">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                            <input type="file" id="foto_qpoint" name="foto_qpoint" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold mb-2">
                            <i class="fas fa-chart-bar mr-1"></i>Foto Limit Sampel
                        </label>
                        <div class="camera-container">
                            <video id="camera-limit_sampel" class="camera-preview" autoplay playsinline style="display: none;"></video>
                            <canvas id="canvas-limit_sampel" class="camera-canvas" style="display: none;"></canvas>
                            <div id="preview-limit_sampel" class="camera-preview-container" style="display: none;">
                                <img id="limit_sampel_preview_img" src="" alt="Preview Limit Sampel" class="img-fluid rounded">
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="limit_sampel">
                                    <i class="fas fa-camera mr-1"></i>Buka Kamera
                                </button>
                                <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="limit_sampel" style="display: none;">
                                    <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="limit_sampel" style="display: none;">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                            <input type="file" id="foto_limit_sampel" name="foto_limit_sampel" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AMTPM Section -->
        <hr>
        <h6 class="font-weight-bold">Standar AMTPM</h6>
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-group">
                    <label>1. Apakah terdapat Standar AMTPM?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="amptm_standar" id="amptm_standar_ya" value="ya" required>
                        <label class="form-check-label" for="amptm_standar_ya">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="amptm_standar" id="amptm_standar_tidak" value="tidak">
                        <label class="form-check-label" for="amptm_standar_tidak">Tidak</label>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#amptmExampleModal">
                            <i class="fas fa-eye mr-1"></i>Lihat Contoh Standar AMTPM
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold mb-2">
                            <i class="fas fa-clipboard-list mr-1"></i>Foto Standar AMTPM
                        </label>
                        <div class="camera-container">
                            <video id="camera-amptm" class="camera-preview" autoplay playsinline style="display: none;"></video>
                            <canvas id="canvas-amptm" class="camera-canvas" style="display: none;"></canvas>
                            <div id="preview-amptm" class="camera-preview-container" style="display: none;">
                                <img id="amptm_preview_img" src="" alt="Preview Standar AMTPM" class="img-fluid rounded">
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="amptm">
                                    <i class="fas fa-camera mr-1"></i>Buka Kamera
                                </button>
                                <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="amptm" style="display: none;">
                                    <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="amptm" style="display: none;">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                            <input type="file" id="foto_amptm" name="foto_amptm" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Simpan Checksheet
        </button>
    </div>
</form>

<!-- AMTPM Example Modal -->
<div class="modal fade" id="amptmExampleModal" tabindex="-1" role="dialog" aria-labelledby="amptmExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amptmExampleModalLabel">
                    <i class="fas fa-clipboard-list mr-2"></i>Contoh Standar AMTPM
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Petunjuk:</strong> Berikut adalah contoh dokumen Standar AMTPM yang dapat digunakan sebagai referensi.
                </div>
                <div class="pdf-container" style="height: 500px;">
                    <iframe src="{{ asset('images/barang/1AMTPM.pdf') }}" 
                            width="100%" 
                            height="100%" 
                            style="border: 1px solid #ddd; border-radius: 5px;">
                        <p>Browser Anda tidak mendukung tampilan PDF. 
                        <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank">Klik di sini untuk mengunduh</a></p>
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ asset('images/barang/1AMTPM.pdf') }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i>Unduh PDF
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/checksheet-create.css') }}">
<script src="{{ asset('js/checksheet-create.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const currentHour = now.getHours();
        let selectedShift = '';

        if (currentHour >= 7 && currentHour < 15) {
            selectedShift = 'shift_1';
        } else if (currentHour >= 15 && currentHour < 23) {
            selectedShift = 'shift_2';
        } else {
            selectedShift = 'shift_3';
        }

        const shiftSelect = document.getElementById('shift');
        if (shiftSelect) {
            shiftSelect.value = selectedShift;
        }
    });
</script>
