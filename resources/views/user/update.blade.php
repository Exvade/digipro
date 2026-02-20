<form id="editForm" action="{{ route('user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- NIK -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="nik" class="form-label">
                    NIK <span class="text-danger">*</span>
                </label>
                <input type="text" name="nik" id="nik"
                    value="{{ old('nik', $user->nik) }}"
                    class="form-control @error('nik') is-invalid @enderror"
                    placeholder="Masukkan NIK" required>
                @error('nik')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Username -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="username" class="form-label">
                    Username <span class="text-danger">*</span>
                </label>
                <input type="text" name="username" id="username"
                    value="{{ old('username', $user->username) }}"
                    class="form-control @error('username') is-invalid @enderror"
                    placeholder="Masukkan username">
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Departemen -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="departemen" class="form-label">Departemen</label>
                <input type="text" name="departemen" id="departemen"
                    value="{{ old('departemen', $user->departemen) }}"
                    class="form-control @error('departemen') is-invalid @enderror"
                    placeholder="Masukkan departemen">
                @error('departemen')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Nama Lengkap -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="nama" class="form-label">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama" id="nama"
                    value="{{ old('nama', $user->nama) }}"
                    class="form-control @error('nama') is-invalid @enderror"
                    placeholder="Masukkan nama lengkap">
                @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" name="email" id="email"
                    value="{{ old('email', $user->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="Masukkan email">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Role -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="role" class="form-label">
                    Role <span class="text-danger">*</span>
                </label>
                <select name="role" id="role"
                    class="form-control @error('role') is-invalid @enderror">
                    <option value="">Pilih role</option>
                    @foreach (['super_admin'=>'Super Admin','leader'=>'Leader','operator'=>'Operator',
                    'foreman'=>'Foreman','kabag'=>'Kabag','staff'=>'Staff',
                    'manager_plant'=>'Manager Plant'] as $val => $label)
                    <option value="{{ $val }}" {{ old('role', $user->role) == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Bagian -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="bagian" class="form-label">Bagian</label>
                <select name="bagian" id="bagian"
                    class="form-control @error('bagian') is-invalid @enderror">
                    <option value="">Pilih bagian (opsional)</option>
                    <option value="inject" {{ old('bagian', $user->bagian) == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ old('bagian', $user->bagian) == 'assy' ? 'selected' : '' }}>Assy</option>
                </select>
                @error('bagian')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Grup -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="grup_id" class="form-label">Grup</label>
                <select name="grup_id" id="grup_id"
                    class="form-control @error('grup_id') is-invalid @enderror">
                    <option value="">Pilih grup (opsional)</option>
                    @foreach ($grups as $grup)
                    <option value="{{ $grup->id }}"
                        {{ old('grup_id', $user->grup_id) == $grup->id ? 'selected' : '' }}>
                        {{ $grup->nama_grup }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Pilih grup kerja user
                </small>
                @error('grup_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Kosongkan jika tidak ingin mengubah">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Konfirmasi Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password Baru
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    placeholder="Konfirmasi password baru">
                @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Tombol -->
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary ml-2">Update</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Submit form via AJAX
        $('#editForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'User berhasil diperbarui.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#editModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Terjadi kesalahan saat menyimpan data.'
                        });
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors;
                    if (errors) {
                        form.find('.is-invalid').removeClass('is-invalid');
                        form.find('.invalid-feedback').html('');

                        $.each(errors, function(field, messages) {
                            var input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').html(messages[0]);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menyimpan data.'
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Clear error saat input berubah
        $('#editForm input, #editForm select').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').html('');
        });

        // Filter grup berdasarkan bagian
        $('#bagian').on('change', function() {
            var bagian = $(this).val();
            var grupSelect = $('#grup_id');

            if (bagian !== '{{ $user->bagian }}') {
                grupSelect.val('');
            }

            if (bagian) {
                grupSelect.find('option').hide();
                grupSelect.find('option:first').show();
                grupSelect.find('option').each(function() {
                    var option = $(this);
                    var grupText = option.text().toLowerCase();
                    if (grupText.includes(bagian.toLowerCase()) || option.val() === '') {
                        option.show();
                    }
                });
            } else {
                grupSelect.find('option').show();
            }
        });
    });
</script>