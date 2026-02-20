<!-- This form is intended to be loaded via AJAX into a modal, not as a standalone page. -->

<form id="addForm" action="{{ route('mesin.store') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="nama_mesin">
            Nama Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" name="nama_mesin" id="nama_mesin" value="{{ old('nama_mesin') }}" class="form-control"
            placeholder="Masukkan nama mesin" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="brand_mesin">
            Brand Mesin <span class="text-danger">*</span>
        </label>
        <input type="text" name="brand_mesin" id="brand_mesin" value="{{ old('brand_mesin') }}" class="form-control"
            placeholder="Masukkan brand mesin" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="tonase_mesin">
            Tonase Mesin <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input type="number" name="tonase_mesin" id="tonase_mesin" value="{{ old('tonase_mesin') }}"
                class="form-control" placeholder="Masukkan tonase mesin" step="0.01" min="0" max="999999"
                required>
            <div class="input-group-append">
                <span class="input-group-text">Ton</span>
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="status">
            Status <span class="text-danger">*</span>
        </label>
        <select name="status" id="status" class="form-control" required>
            <option value="">Pilih status</option>
            <option value="running" {{ old('status') == 'running' ? 'selected' : '' }}>Running</option>
            <option value="stop" {{ old('status') == 'stop' ? 'selected' : '' }}>Stand By</option>
            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Breakdown</option>
            <option value="offline" {{ old('status') == 'offline' ? 'selected' : '' }}>Off</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group" id="stop_reason_wrapper" style="display: none;">
        <label for="stop_reason">
            Pesan Status <span class="text-danger">*</span>
        </label>
        <select name="stop_reason" id="stop_reason" class="form-control">
            <option value="">Pilih alasan</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group">
        <label for="keterangan">
            Keterangan
        </label>
        <textarea name="keterangan" id="keterangan" class="form-control" placeholder="Masukkan keterangan (opsional)">{{ old('keterangan') }}</textarea>
        <div class="invalid-feedback"></div>
    </div>

    <script>
        $(document).ready(function() {
            const reasons = {
                'maintenance': [{
                        value: 'Mesin Error',
                        text: 'Mesin Error'
                    },
                    {
                        value: 'Leaking / nozzle meleleh',
                        text: 'Leaking / nozzle meleleh'
                    },
                    {
                        value: 'Nozel tersumbat',
                        text: 'Nozel tersumbat'
                    },
                    {
                        value: 'Crane / Hoist Error',
                        text: 'Crane / Hoist Error'
                    },
                    {
                        value: 'Robot Error',
                        text: 'Robot Error'
                    },
                    {
                        value: 'MTC Error',
                        text: 'MTC Error'
                    },
                    {
                        value: 'Control Hot Runner Error',
                        text: 'Control Hot Runner Error'
                    },
                    {
                        value: 'Control Valve Gate Error',
                        text: 'Control Valve Gate Error'
                    },
                    {
                        value: 'Cooling Tower Mati',
                        text: 'Cooling Tower Mati'
                    },
                    {
                        value: 'Chiller Mati',
                        text: 'Chiller Mati'
                    },
                    {
                        value: 'Jig Proses Trouble',
                        text: 'Jig Proses Trouble'
                    },
                    {
                        value: 'Mold Trouble',
                        text: 'Mold Trouble'
                    },
                    {
                        value: 'Trial Material',
                        text: 'Trial Material'
                    },
                    {
                        value: 'Trial New Model / New Mold',
                        text: 'Trial New Model / New Mold'
                    },
                    {
                        value: 'Trial Over',
                        text: 'Trial Over'
                    }
                ],
                'stop': [{
                        value: 'Cuci Barel',
                        text: 'Cuci Barel'
                    },
                    {
                        value: 'Material Habis',
                        text: 'Material Habis'
                    },
                    {
                        value: 'Telat Supply',
                        text: 'Telat Supply'
                    },
                    {
                        value: 'Kontaminasi Material Lain',
                        text: 'Kontaminasi Material Lain'
                    },
                    {
                        value: 'Drying Ulang Material',
                        text: 'Drying Ulang Material'
                    },
                    {
                        value: 'Sarana Packing Habis',
                        text: 'Sarana Packing Habis'
                    },
                    {
                        value: 'Sarana Packing Telat Supply',
                        text: 'Sarana Packing Telat Supply'
                    },
                    {
                        value: 'Setup Mold',
                        text: 'Setup Mold'
                    },
                    {
                        value: 'Set Down Mold',
                        text: 'Set Down Mold'
                    },
                    {
                        value: 'Setting Parameter',
                        text: 'Setting Parameter'
                    },
                    {
                        value: 'Dokumen Standar Tidak Tersedia',
                        text: 'Dokumen Standar Tidak Tersedia'
                    },
                    {
                        value: 'Tunggu Judgment Quality',
                        text: 'Tunggu Judgment Quality'
                    },
                    {
                        value: 'Tidak Ada Operator',
                        text: 'Tidak Ada Operator'
                    },
                    {
                        value: 'Man Power On The Job Training',
                        text: 'Man Power On The Job Training'
                    },
                    {
                        value: 'Man Power 5R',
                        text: 'Man Power 5R'
                    }
                ],
                'offline': [{
                    value: 'Tidak ada jadwal produksi',
                    text: 'Tidak ada jadwal produksi'
                }]
            };

            $('#status', '#addForm').on('change', function() {
                const status = $(this).val();
                const $wrapper = $('#stop_reason_wrapper');
                const $select = $('#stop_reason');

                if (reasons[status]) {
                    $select.html('<option value="">Pilih alasan</option>');
                    reasons[status].forEach(function(item) {
                        $select.append(`<option value="${item.value}">${item.text}</option>`);
                    });
                    $wrapper.slideDown();
                    $select.attr('required', true);
                } else {
                    $wrapper.slideUp();
                    $select.attr('required', false).val('');
                }
            });
        });
    </script>

    <div class="text-right">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="btn btn-primary">
            Simpan
        </button>
    </div>
</form>