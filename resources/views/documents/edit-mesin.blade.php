<form id="editMesinFormDashboard" method="POST" action="{{ route('mesin.update', $mesin->id) }}">
    @csrf
    @method('PUT')
    @php
    $role = Auth::user()->role;
    $isLeader = in_array($role, ['leader', 'foreman']);
    @endphp

    <div class="form-group">
        <label for="nama_mesin">Nama Mesin <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nama_mesin" name="nama_mesin" value="{{ $mesin->nama_mesin }}"
            required {{ $isLeader ? 'readonly' : '' }}>
    </div>

    <div class="form-group">
        <label for="brand_mesin">Brand Mesin <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="brand_mesin" name="brand_mesin" value="{{ $mesin->brand_mesin }}"
            required {{ $isLeader ? 'readonly' : '' }}>
    </div>

    <div class="form-group">
        <label for="tonase_mesin">Tonase Mesin (Ton) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="tonase_mesin" name="tonase_mesin"
            value="{{ $mesin->tonase_mesin }}" step="0.01" min="0" required {{ $isLeader ? 'readonly' : '' }}>
    </div>

    <div class="form-group">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select class="form-control" id="status" name="status" required>
            <option value="running" {{ $mesin->status == 'running' ? 'selected' : '' }}>Running</option>
            <option value="stop" {{ $mesin->status == 'stop' ? 'selected' : '' }}>Stand By</option>
            <option value="maintenance" {{ $mesin->status == 'maintenance' ? 'selected' : '' }}>Breakdown</option>
            <option value="offline" {{ $mesin->status == 'offline' ? 'selected' : '' }}>Off</option>
        </select>
    </div>

    <div class="form-group {{ in_array($mesin->status, ['stop', 'maintenance', 'offline']) ? '' : 'd-none' }}" id="stop_reason_wrapper">
        <label for="stop_reason">Pesan Status <span class="text-danger">*</span></label>
        <select class="form-control" id="stop_reason" name="stop_reason" {{ in_array($mesin->status, ['stop', 'maintenance', 'offline']) ? 'required' : '' }}>
            <option value="">Pilih alasan</option>
        </select>
    </div>

    <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan (opsional)">{{ $mesin->keterangan }}</textarea>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Simpan Perubahan
        </button>
    </div>
</form>

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

        const currentStatus = '{{ $mesin->status }}';
        const currentReason = '{{ $mesin->stop_reason }}';

        function updateReasons(status, selectedReason = '') {
            const $wrapper = $('#stop_reason_wrapper');
            const $select = $('#stop_reason');

            if (reasons[status]) {
                $select.html('<option value="">Pilih alasan</option>');
                reasons[status].forEach(function(item) {
                    const selected = item.value === selectedReason ? 'selected' : '';
                    $select.append(`<option value="${item.value}" ${selected}>${item.text}</option>`);
                });
                $wrapper.removeClass('d-none').slideDown();
                $select.prop('required', true);
            } else {
                $wrapper.slideUp(function() {
                    $(this).addClass('d-none');
                });
                $select.prop('required', false).val('');
            }
        }

        // Initial load
        updateReasons(currentStatus, currentReason);

        // Toggle fields based on status change
        $('#status').on('change', function() {
            updateReasons($(this).val());
        });

        // Handle form submission
        $(document).off('submit', '#editMesinFormDashboard').on('submit', '#editMesinFormDashboard', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                beforeSend: function() {
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        $('#editMesinModal').modal('hide');

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data mesin berhasil diperbarui.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Reload page to update machine cards
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    let errorMessage = 'Terjadi kesalahan saat menyimpan data!';

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                }
            });
        });
    });
</script>