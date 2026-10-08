<form id="editForm" class="user-edit-form space-y-5" action="{{ route('user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- NIK -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="nik" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                NIK <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-id-badge text-xs"></i>
                </span>
                <input type="text" name="nik" id="nik"
                    value="{{ old('nik', $user->nik) }}"
                    class="form-control w-full pl-9 pr-3 py-2 text-xs font-mono font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('nik') is-invalid @enderror"
                    placeholder="Masukkan NIK" required>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('nik') {{ $message }} @enderror
            </div>
        </div>

        <!-- Username -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="username" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Username <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-user text-xs"></i>
                </span>
                <input type="text" name="username" id="username"
                    value="{{ old('username', $user->username) }}"
                    class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('username') is-invalid @enderror"
                    placeholder="Masukkan username" required>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('username') {{ $message }} @enderror
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="nama" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-signature text-xs"></i>
                </span>
                <input type="text" name="nama" id="nama"
                    value="{{ old('nama', $user->nama) }}"
                    class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('nama') is-invalid @enderror"
                    placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('nama') {{ $message }} @enderror
            </div>
        </div>

        <!-- Departemen -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="departemen" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Departemen <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-building text-xs"></i>
                </span>
                <input type="text" name="departemen" id="departemen"
                    value="{{ old('departemen', $user->departemen) }}"
                    class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('departemen') is-invalid @enderror"
                    placeholder="Contoh: Produksi, IT, Quality" required>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('departemen') {{ $message }} @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="form-group space-y-1.5 mb-0 sm:col-span-2">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Alamat Email <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-envelope text-xs"></i>
                </span>
                <input type="email" name="email" id="email"
                    value="{{ old('email', $user->email) }}"
                    class="form-control w-full pl-9 pr-3 py-2 text-xs font-mono font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('email') is-invalid @enderror"
                    placeholder="user@example.com" required>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('email') {{ $message }} @enderror
            </div>
        </div>

        <!-- Role -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="role" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Peran (Role) <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-shield-alt text-xs"></i>
                </span>
                <select name="role" id="role"
                    class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('role') is-invalid @enderror" required>
                    <option value="">-- Pilih Peran --</option>
                    @foreach (['super_admin'=>'Super Admin','leader'=>'Leader','operator'=>'Operator',
                    'foreman'=>'Foreman','kabag'=>'Kabag','staff'=>'Staff',
                    'manager_plant'=>'Manager Plant'] as $val => $label)
                    <option value="{{ $val }}" {{ old('role', $user->role) == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('role') {{ $message }} @enderror
            </div>
        </div>

        <!-- Bagian -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="bagian" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Bagian Produksi <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-industry text-xs"></i>
                </span>
                <select name="bagian" id="bagian"
                    class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('bagian') is-invalid @enderror">
                    <option value="">-- Tanpa Bagian --</option>
                    <option value="inject" {{ old('bagian', $user->bagian) == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ old('bagian', $user->bagian) == 'assy' ? 'selected' : '' }}>Assy</option>
                </select>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('bagian') {{ $message }} @enderror
            </div>
        </div>

        <!-- Grup -->
        <div class="form-group space-y-1.5 mb-0 sm:col-span-2">
            <label for="grup_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Grup Kerja / Regu <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-layer-group text-xs"></i>
                </span>
                <select name="grup_id" id="grup_id"
                    class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('grup_id') is-invalid @enderror">
                    <option value="">-- Pilih Grup Kerja --</option>
                    @foreach ($grups as $grup)
                    <option value="{{ $grup->id }}" data-bagian="{{ $grup->bagian }}"
                        {{ old('grup_id', $user->grup_id) == $grup->id ? 'selected' : '' }}>
                        {{ $grup->nama_grup }} @if($grup->bagian) ({{ strtoupper($grup->bagian) }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 mb-0">
                <i class="fas fa-info-circle mr-1 text-sky-400"></i>Opsi grup disesuaikan secara otomatis jika memilih Bagian Produksi.
            </p>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('grup_id') {{ $message }} @enderror
            </div>
        </div>

        <!-- Password Baru -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="password_edit" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Kata Sandi Baru <span class="text-[10px] font-normal text-slate-400">(Kosongkan jika tetap)</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-lock text-xs"></i>
                </span>
                <input type="password" name="password" id="password_edit"
                    class="form-control w-full pl-9 pr-10 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('password') is-invalid @enderror"
                    placeholder="Kosongkan jika tidak diubah">
                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="toggleEditPassword('password_edit', this)">
                    <i class="fas fa-eye text-xs"></i>
                </button>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('password') {{ $message }} @enderror
            </div>
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="password_confirmation_edit" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Konfirmasi Kata Sandi Baru
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-shield-halved text-xs"></i>
                </span>
                <input type="password" name="password_confirmation" id="password_confirmation_edit"
                    class="form-control w-full pl-9 pr-10 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all @error('password_confirmation') is-invalid @enderror"
                    placeholder="Ulangi kata sandi baru jika diubah">
                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="toggleEditPassword('password_confirmation_edit', this)">
                    <i class="fas fa-eye text-xs"></i>
                </button>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('password_confirmation') {{ $message }} @enderror
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5">
        <button type="button" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all cursor-pointer" data-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 active:bg-sky-700 shadow-md shadow-sky-600/25 transition-all cursor-pointer">
            <i class="fas fa-check text-xs"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>

<script>
    function toggleEditPassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    $(document).ready(function() {
        // Submit form via AJAX
        $('#editForm').off('submit').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalHtml = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin text-xs"></i> Menyimpan...');

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
                            input.closest('.form-group').find('.invalid-feedback').html(messages[0]);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.'
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Clear error saat input berubah
        $('#editForm input, #editForm select').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).closest('.form-group').find('.invalid-feedback').html('');
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
                    var optionBagian = option.data('bagian');
                    if ((optionBagian && optionBagian.toLowerCase() === bagian.toLowerCase()) || 
                        grupText.includes(bagian.toLowerCase()) || 
                        option.val() === '') {
                        option.show();
                    }
                });
            } else {
                grupSelect.find('option').show();
            }
        });
    });
</script>