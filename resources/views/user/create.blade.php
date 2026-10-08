<!-- Form Create User -->
<form id="addForm" class="user-create-form space-y-5" action="{{ route('user.store') }}" method="POST">
    @csrf

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
                       value="{{ old('nik') }}"
                       class="form-control w-full pl-9 pr-3 py-2 text-xs font-mono font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('nik') is-invalid @enderror"
                       placeholder="Contoh: 12345" required>
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
                       value="{{ old('username') }}"
                       class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('username') is-invalid @enderror"
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
                       value="{{ old('nama') }}"
                       class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('nama') is-invalid @enderror"
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
                       value="{{ old('departemen') }}"
                       class="form-control w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('departemen') is-invalid @enderror"
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
                       value="{{ old('email') }}"
                       class="form-control w-full pl-9 pr-3 py-2 text-xs font-mono font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('email') is-invalid @enderror"
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
                        class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('role') is-invalid @enderror" required>
                    <option value="">-- Pilih Peran --</option>
                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="leader" {{ old('role') == 'leader' ? 'selected' : '' }}>Leader</option>
                    <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="foreman" {{ old('role') == 'foreman' ? 'selected' : '' }}>Foreman</option>
                    <option value="kabag" {{ old('role') == 'kabag' ? 'selected' : '' }}>Kabag</option>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager_plant" {{ old('role') == 'manager_plant' ? 'selected' : '' }}>Manager Plant</option>
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
                        class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('bagian') is-invalid @enderror">
                    <option value="" selected>-- Tanpa Bagian --</option>
                    <option value="inject" {{ old('bagian') == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ old('bagian') == 'assy' ? 'selected' : '' }}>Assy</option>
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
                        class="form-control w-full pl-9 pr-8 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('grup_id') is-invalid @enderror">
                    <option value="" selected>-- Pilih Grup Kerja --</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->id }}" data-bagian="{{ $grup->bagian }}"
                            {{ old('grup_id') == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }} @if($grup->bagian) ({{ strtoupper($grup->bagian) }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 mb-0">
                <i class="fas fa-info-circle mr-1 text-indigo-400"></i>Opsi grup disesuaikan secara otomatis jika memilih Bagian Produksi.
            </p>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('grup_id') {{ $message }} @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-lock text-xs"></i>
                </span>
                <input type="password" name="password" id="password"
                       class="form-control w-full pl-9 pr-10 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('password') is-invalid @enderror"
                       placeholder="Minimal 8 karakter" required>
                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="togglePasswordVisibility('password', this)">
                    <i class="fas fa-eye text-xs"></i>
                </button>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium">
                @error('password') {{ $message }} @enderror
            </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group space-y-1.5 mb-0">
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fas fa-shield-halved text-xs"></i>
                </span>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control w-full pl-9 pr-10 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                       placeholder="Ulangi kata sandi" required>
                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="togglePasswordVisibility('password_confirmation', this)">
                    <i class="fas fa-eye text-xs"></i>
                </button>
            </div>
            <div class="invalid-feedback text-rose-500 text-[11px] mt-1 font-medium"></div>
        </div>
    </div>

    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5">
        <button type="button" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all cursor-pointer" data-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-save text-xs"></i>
            <span>Simpan Pengguna</span>
        </button>
    </div>
</form>

<script>
    function togglePasswordVisibility(fieldId, btn) {
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
</script>
