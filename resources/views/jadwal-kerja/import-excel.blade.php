<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-lg shadow-sm">
            <form id="importExcelForm" method="POST" action="{{ route('jadwal-kerja.import') }}" enctype="multipart/form-data">
                @csrf

                <!-- Header -->
                <div class="modal-header bg-success text-white border-0 rounded-top">
                    <h5 class="modal-title font-weight-bold d-flex align-items-center" id="importExcelModalLabel">
                        <i class="fas fa-file-excel mr-2"></i> Import Jadwal Kerja dari Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <!-- Download Template -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle mt-1 mr-2"></i>
                            <div>
                                <strong>Langkah-langkah Import:</strong>
                                <ol class="mb-2 pl-3">
                                    <li>Download template Excel terlebih dahulu</li>
                                    <li>Isi data jadwal kerja sesuai format template</li>
                                    <li>Upload file Excel yang sudah diisi</li>
                                </ol>
                                <a href="{{ route('jadwal-kerja.download-template') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download mr-1"></i> Download Template Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="excel_file" class="font-weight-semibold">
                            Upload File Excel <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file"
                                accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="excel_file">Pilih file Excel...</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Format file: .xlsx atau .xls (maksimal 2MB)
                        </small>
                    </div>

                    <!-- Preview Info -->
                    <div id="file-info" class="alert alert-secondary" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-excel text-success mr-2"></i>
                                <strong>File:</strong> <span id="file-name"></span>
                            </div>
                            <div>
                                <strong>Ukuran:</strong> <span id="file-size"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Format Template Info -->
                    <div class="card border-warning mt-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-table text-warning"></i> Format Template Excel
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Shift</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-muted">
                                            <td>1234567890</td>
                                            <td>John Doe</td>
                                            <td>2025-12-10</td>
                                            <td>pagi</td>
                                            <td>aktif</td>
                                            <td>Normal</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <strong>Catatan:</strong>
                                <ul class="mb-0 pl-3">
                                    <li>NIK: Nomor Induk Karyawan (harus sudah terdaftar di sistem)</li>
                                    <li>Nama: Nama karyawan (untuk referensi)</li>
                                    <li>Shift: pagi, siang, malam, atau off</li>
                                    <li>Status: aktif atau tidak_aktif</li>
                                    <li>Tanggal format: YYYY-MM-DD (contoh: 2025-12-10)</li>
                                    <li><strong class="text-danger">Jika NIK tidak ditemukan, baris akan dilewati</strong></li>
                                </ul>
                            </small>
                        </div>
                    </div>

                    <!-- Alert untuk NIK tidak ditemukan -->
                    <div class="alert alert-warning mt-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle mt-1 mr-2"></i>
                            <div>
                                <strong>Perhatian:</strong>
                                <p class="mb-0">Jika ada NIK yang tidak ditemukan dalam sistem, baris tersebut akan <strong>dilewati</strong> dan tidak diimport. Pastikan semua karyawan sudah terdaftar di Database Karyawan sebelum melakukan import.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light border-0 rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-upload mr-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle file input change
        const fileInput = document.getElementById('excel_file');
        const fileLabel = document.querySelector('.custom-file-label');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Update label
                    fileLabel.textContent = file.name;

                    // Show file info
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileInfo.style.display = 'block';

                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB');
                        fileInput.value = '';
                        fileLabel.textContent = 'Pilih file Excel...';
                        fileInfo.style.display = 'none';
                    }

                    // Validate file extension
                    const validExtensions = ['xlsx', 'xls'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (!validExtensions.includes(fileExtension)) {
                        alert('Format file tidak valid! Gunakan file .xlsx atau .xls');
                        fileInput.value = '';
                        fileLabel.textContent = 'Pilih file Excel...';
                        fileInfo.style.display = 'none';
                    }
                } else {
                    fileLabel.textContent = 'Pilih file Excel...';
                    fileInfo.style.display = 'none';
                }
            });
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Reset form when modal is closed
        $('#importExcelModal').on('hidden.bs.modal', function() {
            document.getElementById('importExcelForm').reset();
            fileLabel.textContent = 'Pilih file Excel...';
            fileInfo.style.display = 'none';
        });
    });
</script>