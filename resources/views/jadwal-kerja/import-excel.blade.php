<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
            <form id="importExcelForm" method="POST" action="{{ route('jadwal-kerja.import') }}" enctype="multipart/form-data">
                @csrf

                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent dark:from-emerald-500/20 border-b border-emerald-200/50 dark:border-emerald-900/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div>
                            <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="importExcelModalLabel">
                                Import Jadwal Kerja dari Excel
                            </h5>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Upload file template excel untuk input jadwal massal</p>
                        </div>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <!-- Langkah-langkah Download Template -->
                    <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-800/60 flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="flex-1 min-w-0 text-xs">
                            <span class="font-bold text-indigo-900 dark:text-indigo-200 block mb-1">Panduan Pengisian:</span>
                            <ol class="list-decimal list-inside text-indigo-800/90 dark:text-indigo-300 space-y-0.5 mb-2.5">
                                <li>Download file template Excel yang sudah disediakan.</li>
                                <li>Isi jadwal kerja sesuai kolom NIK, Nama, Tanggal, Shift, dan Status.</li>
                                <li>Upload kembali file Excel yang telah dilengkapi.</li>
                            </ol>
                            <a href="{{ route('jadwal-kerja.download-template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-sm transition-all">
                                <i class="fas fa-download text-[10px]"></i>
                                <span>Download Template Excel</span>
                            </a>
                        </div>
                    </div>

                    <!-- File Upload Box -->
                    <div>
                        <label for="excel_file" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Pilih File Excel <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 rounded-2xl p-4 text-center transition-all bg-slate-50 dark:bg-slate-800/50 cursor-pointer">
                            <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                            <div class="space-y-1">
                                <i class="fas fa-file-excel text-3xl text-emerald-500 mb-1"></i>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200 custom-file-label">
                                    Klik atau seret file Excel ke sini...
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Format: <strong>.xlsx</strong> atau <strong>.xls</strong> (Maks. 2MB)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview File Info -->
                    <div id="file-info" class="p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60" style="display: none;">
                        <div class="flex items-center justify-between text-xs text-emerald-900 dark:text-emerald-200">
                            <div class="flex items-center gap-2 truncate">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span class="font-bold truncate" id="file-name">-</span>
                            </div>
                            <span class="font-semibold text-emerald-700 dark:text-emerald-300 text-[11px] px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/60" id="file-size">-</span>
                        </div>
                    </div>

                    <!-- Template Format Table -->
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden text-xs">
                        <div class="px-3 py-2 bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 text-[11px] uppercase tracking-wider">
                            <i class="fas fa-table text-slate-400"></i> Contoh Format Kolom Excel
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[11px]">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="py-1.5 px-2.5 font-bold">NIK</th>
                                        <th class="py-1.5 px-2.5 font-bold">Nama</th>
                                        <th class="py-1.5 px-2.5 font-bold">Tanggal</th>
                                        <th class="py-1.5 px-2.5 font-bold">Shift</th>
                                        <th class="py-1.5 px-2.5 font-bold">Status</th>
                                        <th class="py-1.5 px-2.5 font-bold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-700 dark:text-slate-300 divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr>
                                        <td class="py-1 px-2.5 font-mono">12345678</td>
                                        <td class="py-1 px-2.5">Budi Santoso</td>
                                        <td class="py-1 px-2.5 font-mono">2026-10-05</td>
                                        <td class="py-1 px-2.5"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold">pagi</span></td>
                                        <td class="py-1 px-2.5"><span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 font-bold">aktif</span></td>
                                        <td class="py-1 px-2.5 text-slate-400">Reguler</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Warning Alert NIK -->
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div>
                            <strong>Perhatian:</strong> Pastikan NIK sudah terdaftar dalam data master User. Baris dengan NIK yang tidak ditemukan akan otomatis dilewati saat proses import.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                        <i class="fas fa-times text-[10px]"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 transition-all cursor-pointer">
                        <i class="fas fa-upload text-[10px]"></i>
                        <span>Upload & Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('excel_file');
        const fileLabel = document.querySelector('.custom-file-label');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (fileLabel) fileLabel.textContent = file.name;
                    if (fileName) fileName.textContent = file.name;
                    if (fileSize) fileSize.textContent = formatFileSize(file.size);
                    if (fileInfo) fileInfo.style.display = 'block';

                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB');
                        fileInput.value = '';
                        if (fileLabel) fileLabel.textContent = 'Klik atau seret file Excel ke sini...';
                        if (fileInfo) fileInfo.style.display = 'none';
                        return;
                    }

                    // Validate file extension
                    const validExtensions = ['xlsx', 'xls'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (!validExtensions.includes(fileExtension)) {
                        alert('Format file tidak valid! Gunakan file .xlsx atau .xls');
                        fileInput.value = '';
                        if (fileLabel) fileLabel.textContent = 'Klik atau seret file Excel ke sini...';
                        if (fileInfo) fileInfo.style.display = 'none';
                    }
                } else {
                    if (fileLabel) fileLabel.textContent = 'Klik atau seret file Excel ke sini...';
                    if (fileInfo) fileInfo.style.display = 'none';
                }
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        $('#importExcelModal').on('hidden.bs.modal', function() {
            const form = document.getElementById('importExcelForm');
            if (form) form.reset();
            if (fileLabel) fileLabel.textContent = 'Klik atau seret file Excel ke sini...';
            if (fileInfo) fileInfo.style.display = 'none';
        });
    });
</script>