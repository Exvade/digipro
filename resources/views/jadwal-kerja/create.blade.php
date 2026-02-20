<!-- Modal Tambah Jadwal Kerja -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-lg shadow-sm">
            <form id="createForm" method="POST" action="{{ route('jadwal-kerja.store') }}">
                @csrf

                <!-- Header -->
                <div class="modal-header bg-light border-0 rounded-top">
                    <h5 class="modal-title font-weight-bold d-flex align-items-center" id="createModalLabel">
                        <i class="fas fa-calendar-plus mr-2 text-primary"></i> Tambah Jadwal Kerja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="form-row">

                        <!-- Nama Karyawan -->
                        <div class="form-group col-md-6">
                            <label for="user_id" class="font-weight-semibold">Nama Karyawan <span
                                    class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama ?? $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div class="form-group col-md-6">
                            <label for="tanggal" class="font-weight-semibold">Tanggal <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required
                                min="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Shift -->
                        <div class="form-group col-md-6">
                            <label for="shift" class="font-weight-semibold">Shift <span
                                    class="text-danger">*</span></label>
                            <select name="shift" id="shift" class="form-control" required>
                                <option value="">-- Pilih Shift --</option>
                                <option value="pagi">Pagi (07:00 - 15:00)</option>
                                <option value="siang">Siang (15:00 - 23:00)</option>
                                <option value="malam">Malam (23:00 - 07:00)</option>
                                <option value="off">Off (🚫)</option>
                            </select>
                            <small class="form-text text-muted" id="shift-info"></small>
                        </div>

                        <!-- Status -->
                        <div class="form-group col-md-6">
                            <label for="status" class="font-weight-semibold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif">✅ Aktif</option>
                                <option value="tidak_aktif">❌ Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Preview Jam Kerja -->
                        <div class="col-12" id="jam-preview" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-clock mr-2"></i>
                                        <strong>Jam Kerja:</strong> 
                                        <span id="preview-jam-masuk">-</span> s/d <span id="preview-jam-keluar">-</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-custom-jam">
                                        <i class="fas fa-edit"></i> Ubah Jam
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Jam (Hidden by default) -->
                        <div class="col-12" id="custom-jam-section" style="display: none;">
                            <div class="card border-warning mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-clock"></i> Custom Jam Kerja</h6>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-jam">
                                            <i class="fas fa-undo"></i> Reset ke Default
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6 mb-0">
                                            <label for="jam_masuk" class="font-weight-semibold">Jam Masuk</label>
                                            <input type="time" name="jam_masuk" id="jam_masuk" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6 mb-0">
                                            <label for="jam_keluar" class="font-weight-semibold">Jam Keluar</label>
                                            <input type="time" name="jam_keluar" id="jam_keluar" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="form-group col-12">
                            <label for="keterangan" class="font-weight-semibold">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3" maxlength="500" class="form-control"
                                placeholder="Catatan khusus (maks. 500 karakter)"></textarea>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Opsional</small>
                                <small id="charCount" class="text-muted">0/500</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light border-0 rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
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
        if (keterangan) {
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

        // Handle shift change
        shiftSelect.addEventListener('change', function() {
            const selectedShift = this.value;
            
            if (selectedShift && shiftTimes[selectedShift]) {
                const times = shiftTimes[selectedShift];
                
                if (selectedShift === 'off') {
                    // Hide preview for off shift
                    jamPreview.style.display = 'none';
                    customJamSection.style.display = 'none';
                    jamMasukInput.value = '';
                    jamKeluarInput.value = '';
                    shiftInfo.innerHTML = '<i class="fas fa-info-circle text-info"></i> Hari libur - tidak ada jam kerja';
                } else {
                    // Show preview
                    jamPreview.style.display = 'block';
                    previewJamMasuk.textContent = times.jam_masuk;
                    previewJamKeluar.textContent = times.jam_keluar;
                    
                    // Clear custom inputs (will use default from backend)
                    jamMasukInput.value = '';
                    jamKeluarInput.value = '';
                    
                    // Hide custom section
                    customJamSection.style.display = 'none';
                    
                    shiftInfo.innerHTML = `<i class="fas fa-check-circle text-success"></i> Jam kerja otomatis: ${times.jam_masuk} - ${times.jam_keluar}`;
                }
            } else {
                jamPreview.style.display = 'none';
                customJamSection.style.display = 'none';
                shiftInfo.textContent = '';
            }
        });

        // Show custom jam section
        btnCustomJam.addEventListener('click', function() {
            const selectedShift = shiftSelect.value;
            if (selectedShift && shiftTimes[selectedShift] && selectedShift !== 'off') {
                const times = shiftTimes[selectedShift];
                
                // Pre-fill with default times
                jamMasukInput.value = times.jam_masuk;
                jamKeluarInput.value = times.jam_keluar;
                
                // Show custom section
                customJamSection.style.display = 'block';
                
                // Scroll to custom section
                customJamSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        // Reset to default jam
        btnResetJam.addEventListener('click', function() {
            // Clear custom inputs
            jamMasukInput.value = '';
            jamKeluarInput.value = '';
            
            // Hide custom section
            customJamSection.style.display = 'none';
            
            // Update info
            const selectedShift = shiftSelect.value;
            if (selectedShift && shiftTimes[selectedShift]) {
                const times = shiftTimes[selectedShift];
                shiftInfo.innerHTML = `<i class="fas fa-check-circle text-success"></i> Jam kerja otomatis: ${times.jam_masuk} - ${times.jam_keluar}`;
            }
        });

        // Reset form when modal is closed
        $('#createModal').on('hidden.bs.modal', function() {
            document.getElementById('createForm').reset();
            jamPreview.style.display = 'none';
            customJamSection.style.display = 'none';
            shiftInfo.textContent = '';
            counter.textContent = '0/500';
        });
    });
</script>
