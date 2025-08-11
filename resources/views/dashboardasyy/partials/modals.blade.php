<!-- Modal Detail Meja -->
<div class="modal fade" id="mejaDetailModal" tabindex="-1" aria-labelledby="mejaDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mejaDetailModalLabel">
                    <i class="fas fa-table mr-2"></i>Detail Meja Assembly
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Meja Info Section -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <div class="row text-center">
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-table fa-lg text-primary mb-1"></i>
                                    <div class="small font-weight-bold text-primary" id="detail-nama-meja"></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-info-circle fa-lg text-info mb-1"></i>
                                    <div class="small font-weight-bold text-info" id="detail-status-meja"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stepper/Progress Bar -->
                <div class="mb-4">
                    <div class="progress">
                        <div id="stepProgressBar" class="progress-bar" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span id="stepText">Step <span id="stepNow">1</span> / 6 &mdash; <span id="stepPercent">20%</span></span>
                        </div>
                    </div>

                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-2">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clipboard-check mr-2 text-primary"></i>Tambah Checksheet Baru
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="checksheetForm" enctype="multipart/form-data" method="POST" action="{{ route('dashboard.assy.checksheet.store') }}">
                            @csrf
                            <input type="hidden" id="form_meja_id" name="meja_id">
                            <!-- Step 0: Pilih User, Shift, Part, Grup -->
                            <div class="form-step" id="step0">
                                <h5 class="mb-3">Informasi Dasar</h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group mb-0">
                                            <label for="grup" class="small font-weight-bold">
                                                <i class="fas fa-users mr-1"></i>Grup <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-sm" id="grup" name="grup" required>
                                                <option value="">Pilih Grup</option>
                                                <option value="A">Grup A</option>
                                                <option value="B">Grup B</option>
                                                <option value="C">Grup C</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group mb-0">
                                            <label for="user_id" class="small font-weight-bold">
                                                <i class="fas fa-user mr-1"></i>User <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-sm" id="user_id" name="user_id" required>
                                                <option value="">Pilih User</option>
                                                @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->nama }} ({{ $user->username }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group mb-0">
                                            <label for="shift" class="small font-weight-bold">
                                                <i class="fas fa-clock mr-1"></i>Shift <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-sm" id="shift" name="shift" required>
                                                <option value="">Pilih Shift</option>
                                                <option value="shift_1">Shift 1 (07.00-15.00)</option>
                                                <option value="shift_2">Shift 2 (15.00-23.00)</option>
                                                <option value="shift_3">Shift 3 (23.00-07.00)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group mb-0">
                                            <label for="part_id" class="small font-weight-bold">
                                                <i class="fas fa-cube mr-1"></i>Part <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-sm" id="part_id" name="part_id" required>
                                                <option value="">Pilih Part</option>
                                                @foreach($parts as $part)
                                                <option value="{{ $part->id }}">{{ $part->no_parts }} - {{ $part->nama_part }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <!-- ... (isi step0 sama seperti sebelumnya) ... -->
                            </div>
                            <!-- Step 1: APD -->
                            <div class="form-step" id="step1">
                                <div class="text-center mb-3">
                                    <img src="/images/Safetyassy.png" alt="Standar Safety Inject" class="img-fluid rounded" style="max-width: 300px;">
                                    <div class="small text-muted">Contoh Standar Safety Inject</div>
                                </div>
                                <h5 class="mb-3">APD</h5>
                                <div class="form-group">
                                    <label>1. Apakah menggunakan topi?</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_topi" id="apd_topi_ya" value="ya" required>
                                        <label class="form-check-label" for="apd_topi_ya">Ya</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_topi" id="apd_topi_tidak" value="tidak">
                                        <label class="form-check-label" for="apd_topi_tidak">Tidak</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>2. Apakah menggunakan sepatu safety?</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_sepatu" id="apd_sepatu_ya" value="ya" required>
                                        <label class="form-check-label" for="apd_sepatu_ya">Ya</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_sepatu" id="apd_sepatu_tidak" value="tidak">
                                        <label class="form-check-label" for="apd_sepatu_tidak">Tidak</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold mb-2"><i class="fas fa-hard-hat mr-1"></i>Foto APD</label>
                                    <div class="camera-container">
                                        <video id="camera-apd" class="camera-preview" autoplay playsinline style="display: none;"></video>
                                        <canvas id="canvas-apd" class="camera-canvas" style="display: none;"></canvas>
                                        <div id="preview-apd" class="camera-preview-container" style="display: none;">
                                            <img id="apd_preview" src="" alt="Preview APD" class="img-fluid rounded">
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
                           <!-- Step 2: Quality -->
                           <div class="form-step" id="step2" style="display:none;">
                            <h5 class="mb-3">Quality</h5>
                            <div class="form-group">
                                <label>1. Apakah terdapat Q Point?</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="quality_qpoint" id="quality_qpoint_ya" value="ya" required>
                                    <label class="form-check-label" for="quality_qpoint_ya">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="quality_qpoint" id="quality_qpoint_tidak" value="tidak">
                                    <label class="form-check-label" for="quality_qpoint_tidak">Tidak</label>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#qpointExampleModal">
                                        <i class="fas fa-eye mr-1"></i>Lihat Contoh Q-Point
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold mb-2"><i class="fas fa-award mr-1"></i>Foto Quality</label>
                                <div class="camera-container">
                                    <video id="camera-quality" class="camera-preview" autoplay playsinline style="display: none;"></video>
                                    <canvas id="canvas-quality" class="camera-canvas" style="display: none;"></canvas>
                                    <div id="preview-quality" class="camera-preview-container" style="display: none;">
                                        <img id="quality_preview" src="" alt="Preview Quality" class="img-fluid rounded">
                                    </div>
                                    <div class="camera-controls">
                                        <button type="button" class="btn btn-sm btn-primary camera-btn" data-camera="quality">
                                            <i class="fas fa-camera mr-1"></i>Buka Kamera
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success capture-btn" data-camera="quality" style="display: none;">
                                            <i class="fas fa-camera-retro mr-1"></i>Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning retake-btn" data-camera="quality" style="display: none;">
                                            <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                        </button>
                                    </div>
                                    <input type="file" id="foto_quality" name="foto_quality" style="display: none;" accept="image/*">
                                </div>
                            </div>
                        </div>
                            <!-- Step 3: Instruksi Kerja -->
                            <div class="form-step" id="step3" style="display:none;">
                                <h5 class="mb-3">Instruksi Kerja</h5>
                                <div class="form-group">
                                    <label>1. Apakah terdapat Instruksi Kerja?</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="instruksi_kerja" id="instruksi_kerja_ya" value="ya" required>
                                        <label class="form-check-label" for="instruksi_kerja_ya">Ya</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="instruksi_kerja" id="instruksi_kerja_tidak" value="tidak">
                                        <label class="form-check-label" for="instruksi_kerja_tidak">Tidak</label>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#instruksiKerjaExampleModal">
                                            <i class="fas fa-eye mr-1"></i>Lihat Contoh Instruksi Kerja
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold mb-2"><i class="fas fa-file-alt mr-1"></i>Foto Instruksi Kerja</label>
                                    <div class="camera-container">
                                        <video id="camera-instruksi_kerja" class="camera-preview" autoplay playsinline style="display: none;"></video>
                                        <canvas id="canvas-instruksi_kerja" class="camera-canvas" style="display: none;"></canvas>
                                        <div id="preview-instruksi_kerja" class="camera-preview-container" style="display: none;">
                                            <img id="instruksi_kerja_preview" src="" alt="Preview Instruksi Kerja" class="img-fluid rounded">
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
                            <!-- Step 4: Limit Sampel -->
                            <div class="form-step" id="step4" style="display:none;">
                                <h5 class="mb-3">Limit Sampel</h5>
                                <div class="form-group">
                                    <label>1. Apakah terdapat Limit Sampel?</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="limit_sampel" id="limit_sampel_ya" value="ya" required>
                                        <label class="form-check-label" for="limit_sampel_ya">Ya</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="limit_sampel" id="limit_sampel_tidak" value="tidak">
                                        <label class="form-check-label" for="limit_sampel_tidak">Tidak</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold mb-2"><i class="fas fa-chart-bar mr-1"></i>Foto Limit Sampel</label>
                                    <div class="camera-container">
                                        <video id="camera-limit_sampel" class="camera-preview" autoplay playsinline style="display: none;"></video>
                                        <canvas id="canvas-limit_sampel" class="camera-canvas" style="display: none;"></canvas>
                                        <div id="preview-limit_sampel" class="camera-preview-container" style="display: none;">
                                            <img id="limit_sampel_preview" src="" alt="Preview Limit Sampel" class="img-fluid rounded">
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
                            <!-- Step 5: Standar AMTPM -->
                            <div class="form-step" id="step5" style="display:none;">
                                <h5 class="mb-3">Standar AMTPM</h5>
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
                                <div class="form-group">
                                    <label class="font-weight-bold mb-2"><i class="fas fa-clipboard-list mr-1"></i>Foto Standar AMTPM</label>
                                    <div class="camera-container">
                                        <video id="camera-amptm" class="camera-preview" autoplay playsinline style="display: none;"></video>
                                        <canvas id="canvas-amptm" class="camera-canvas" style="display: none;"></canvas>
                                        <div id="preview-amptm" class="camera-preview-container" style="display: none;">
                                            <img id="amptm_preview" src="" alt="Preview Standar AMTPM" class="img-fluid rounded">
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
                            <!-- Step Navigation -->
                            <div class="d-flex justify-content-between mt-4 mb-3">
                                <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display:none;">Sebelumnya</button>
                                <button type="button" class="btn btn-primary ml-auto" id="nextStepBtn">Selanjutnya</button>
                                <button type="submit" class="btn btn-success ml-auto" id="submitStepBtn" style="display:none;">Simpan Checksheet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contoh Q-Point -->
<div class="modal fade" id="qpointExampleModal" tabindex="-1" role="dialog" aria-labelledby="qpointExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qpointExampleModalLabel">
                    <i class="fas fa-award mr-2"></i>Contoh Q-Point
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Petunjuk:</strong> Berikut adalah contoh dokumen Q-Point yang dapat digunakan sebagai referensi.
                </div>
                <div class="pdf-container" style="height: 500px;">
                    <iframe src="{{ asset('images/barang/3Qpoint.pdf') }}" 
                            width="100%" 
                            height="100%" 
                            style="border: 1px solid #ddd; border-radius: 5px;">
                        <p>Browser Anda tidak mendukung tampilan PDF. 
                        <a href="{{ asset('images/barang/3Qpoint.pdf') }}" target="_blank">Klik di sini untuk mengunduh</a></p>
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ asset('images/barang/3Qpoint.pdf') }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i>Unduh PDF
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contoh Instruksi Kerja -->
<div class="modal fade" id="instruksiKerjaExampleModal" tabindex="-1" role="dialog" aria-labelledby="instruksiKerjaExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instruksiKerjaExampleModalLabel">
                    <i class="fas fa-file-alt mr-2"></i>Contoh Instruksi Kerja
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Petunjuk:</strong> Berikut adalah contoh dokumen Instruksi Kerja yang dapat digunakan sebagai referensi.
                </div>
                <div class="pdf-container" style="height: 500px;">
                    <iframe src="{{ asset('images/barang/2IntruksiKerja.pdf') }}" 
                            width="100%" 
                            height="100%" 
                            style="border: 1px solid #ddd; border-radius: 5px;">
                        <p>Browser Anda tidak mendukung tampilan PDF. 
                        <a href="{{ asset('images/barang/2IntruksiKerja.pdf') }}" target="_blank">Klik di sini untuk mengunduh</a></p>
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ asset('images/barang/2IntruksiKerja.pdf') }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i>Unduh PDF
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contoh Standar AMTPM -->
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
