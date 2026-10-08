<!-- Modal Tambah Jadwal Kerja -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
            <form id="createForm" method="POST" action="{{ route('jadwal-kerja.store') }}">
                @csrf

                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500/10 via-indigo-500/5 to-transparent dark:from-indigo-500/20 border-b border-indigo-200/50 dark:border-indigo-900/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div>
                            <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="createModalLabel">
                                Tambah Jadwal Kerja
                            </h5>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Tentukan penugasan shift harian karyawan</p>
                        </div>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Nama Karyawan -->
                        <div>
                            <label for="user_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Nama Karyawan <span class="text-rose-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama ?? $user->name }} ({{ ucfirst($user->bagian ?? 'Produksi') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label for="tanggal" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal" id="tanggal" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all cursor-pointer" required min="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Shift -->
                        <div>
                            <label for="shift" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Shift <span class="text-rose-500">*</span>
                            </label>
                            <select name="shift" id="shift" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" required>
                                <option value="">-- Pilih Shift --</option>
                                <option value="pagi">Pagi (07:00 - 15:00)</option>
                                <option value="siang">Siang (15:00 - 23:00)</option>
                                <option value="malam">Malam (23:00 - 07:00)</option>
                                <option value="off">Off (Libur / 🚫)</option>
                            </select>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1" id="shift-info"></div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Status <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif">✅ Aktif</option>
                                <option value="tidak_aktif">❌ Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Preview Jam Kerja (Hidden by default) -->
                        <div class="col-span-1 md:col-span-2" id="jam-preview" style="display: none;">
                            <div class="p-3.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/70 dark:border-indigo-800/60 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-indigo-900 dark:text-indigo-200">
                                    <i class="fas fa-clock text-indigo-500"></i>
                                    <span><strong>Jam Kerja:</strong> <span id="preview-jam-masuk" class="font-bold">-</span> s/d <span id="preview-jam-keluar" class="font-bold">-</span></span>
                                </div>
                                <button type="button" class="px-3 py-1 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700/80 shadow-sm hover:bg-indigo-50 transition-all cursor-pointer" id="btn-custom-jam">
                                    <i class="fas fa-edit mr-1"></i> Ubah Jam
                                </button>
                            </div>
                        </div>

                        <!-- Custom Jam (Hidden by default) -->
                        <div class="col-span-1 md:col-span-2" id="custom-jam-section" style="display: none;">
                            <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                                        <i class="fas fa-sliders-h text-amber-500"></i> Kustomisasi Jam Kerja
                                    </span>
                                    <button type="button" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-all cursor-pointer" id="btn-reset-jam">
                                        <i class="fas fa-undo mr-1"></i> Reset Default
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="jam_masuk" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Jam Masuk</label>
                                        <input type="time" name="jam_masuk" id="jam_masuk" class="w-full px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label for="jam_keluar" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Jam Keluar</label>
                                        <input type="time" name="jam_keluar" id="jam_keluar" class="w-full px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="keterangan" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Keterangan (Opsional)
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="2" maxlength="500" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" placeholder="Catatan khusus jadwal kerja karyawan..."></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                <span>Maksimal 500 karakter</span>
                                <span id="charCount">0/500</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                        <i class="fas fa-times text-[10px]"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all cursor-pointer">
                        <i class="fas fa-save text-[10px]"></i>
                        <span>Simpan Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counter for keterangan
        const keterangan = document.getElementById('keterangan');
        const counter = document.getElementById('charCount');
        if (keterangan && counter) {
            keterangan.addEventListener('input', function() {
                counter.textContent = `${this.value.length}/500`;
            });
        }

        // Shift time mapping
        const shiftTimes = {
            'pagi': { jam_masuk: '07:00', jam_keluar: '15:00', label: 'Pagi (07:00 - 15:00)' },
            'siang': { jam_masuk: '15:00', jam_keluar: '23:00', label: 'Siang (15:00 - 23:00)' },
            'malam': { jam_masuk: '23:00', jam_keluar: '07:00', label: 'Malam (23:00 - 07:00)' },
            'off': { jam_masuk: null, jam_keluar: null, label: 'Off (Libur)' }
        };

        const shiftSelect = document.getElementById('shift');
        const jamPreview = document.getElementById('jam-preview');
        const customJamSection = document.getElementById('custom-jam-section');
        const previewJamMasuk = document.getElementById('preview-jam-masuk');
        const previewJamKeluar = document.getElementById('preview-jam-keluar');
        const jamMasukInput = document.getElementById('jam_masuk');
        const jamKeluarInput = document.getElementById('jam_keluar');
        const btnCustomJam = document.getElementById('btn-custom-jam');
        const btnResetJam = document.getElementById('btn-reset-jam');
        const shiftInfo = document.getElementById('shift-info');

        if (shiftSelect) {
            shiftSelect.addEventListener('change', function() {
                const selectedShift = this.value;
                
                if (selectedShift && shiftTimes[selectedShift]) {
                    const times = shiftTimes[selectedShift];
                    
                    if (selectedShift === 'off') {
                        if (jamPreview) jamPreview.style.display = 'none';
                        if (customJamSection) customJamSection.style.display = 'none';
                        if (jamMasukInput) jamMasukInput.value = '';
                        if (jamKeluarInput) jamKeluarInput.value = '';
                        if (shiftInfo) shiftInfo.innerHTML = '<span class="text-amber-500 font-semibold"><i class="fas fa-bed mr-1"></i> Hari libur - tidak ada jam kerja</span>';
                    } else {
                        if (jamPreview) jamPreview.style.display = 'block';
                        if (previewJamMasuk) previewJamMasuk.textContent = times.jam_masuk;
                        if (previewJamKeluar) previewJamKeluar.textContent = times.jam_keluar;
                        
                        if (jamMasukInput) jamMasukInput.value = '';
                        if (jamKeluarInput) jamKeluarInput.value = '';
                        if (customJamSection) customJamSection.style.display = 'none';
                        
                        if (shiftInfo) shiftInfo.innerHTML = `<span class="text-emerald-500 font-semibold"><i class="fas fa-check-circle mr-1"></i> Jam kerja otomatis: ${times.jam_masuk} - ${times.jam_keluar}</span>`;
                    }
                } else {
                    if (jamPreview) jamPreview.style.display = 'none';
                    if (customJamSection) customJamSection.style.display = 'none';
                    if (shiftInfo) shiftInfo.textContent = '';
                }
            });
        }

        if (btnCustomJam) {
            btnCustomJam.addEventListener('click', function() {
                const selectedShift = shiftSelect.value;
                if (selectedShift && shiftTimes[selectedShift] && selectedShift !== 'off') {
                    const times = shiftTimes[selectedShift];
                    if (jamMasukInput) jamMasukInput.value = times.jam_masuk;
                    if (jamKeluarInput) jamKeluarInput.value = times.jam_keluar;
                    if (customJamSection) customJamSection.style.display = 'block';
                }
            });
        }

        if (btnResetJam) {
            btnResetJam.addEventListener('click', function() {
                if (jamMasukInput) jamMasukInput.value = '';
                if (jamKeluarInput) jamKeluarInput.value = '';
                if (customJamSection) customJamSection.style.display = 'none';
            });
        }

        $('#createModal').on('hidden.bs.modal', function() {
            const form = document.getElementById('createForm');
            if (form) form.reset();
            if (jamPreview) jamPreview.style.display = 'none';
            if (customJamSection) customJamSection.style.display = 'none';
            if (shiftInfo) shiftInfo.textContent = '';
            if (counter) counter.textContent = '0/500';
        });
    });
</script>
