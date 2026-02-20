<!-- Form Create User -->
<div class="alert alert-success mb-3">
    <i class="fas fa-check-circle mr-2"></i>
    Form create user berhasil dimuat!
</div>

<form id="addForm" class="user-create-form" action="{{ route('user.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- NIK -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="nik" class="form-label">
                    NIK <span class="text-danger">*</span>
                </label>
                <input type="text" name="nik" id="nik"
                       value="{{ old('nik') }}"
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
                       value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror"
                       placeholder="Masukkan username" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Departemen -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="departemen" class="form-label">
                    Departemen <span class="text-danger">*</span>
                </label>
                <input type="text" name="departemen" id="departemen"
                       value="{{ old('departemen') }}"
                       class="form-control @error('departemen') is-invalid @enderror"
                       placeholder="Masukkan departemen" required>
                @error('departemen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i> Pilih departemen kerja user
                </small>
            </div>
        </div>

        <!-- Nama -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="nama" class="form-label">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama" id="nama"
                       value="{{ old('nama') }}"
                       class="form-control @error('nama') is-invalid @enderror"
                       placeholder="Masukkan nama lengkap" required>
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
                       value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Masukkan email" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Role -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="role" class="form-label">
                    Role <span class="text-danger">*</span>
                </label>
                <select name="role" id="role"
                        class="form-control @error('role') is-invalid @enderror" required>
                    <option value="">Pilih role</option>
                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="leader" {{ old('role') == 'leader' ? 'selected' : '' }}>Leader</option>
                    <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="foreman" {{ old('role') == 'foreman' ? 'selected' : '' }}>Foreman</option>
                    <option value="kabag" {{ old('role') == 'kabag' ? 'selected' : '' }}>Kabag</option>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager_plant" {{ old('role') == 'manager_plant' ? 'selected' : '' }}>Manager Plant</option>
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
                    <option value="" selected>Pilih bagian (opsional)</option>
                    <option value="inject" {{ old('bagian') == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ old('bagian') == 'assy' ? 'selected' : '' }}>Assy</option>
                </select>
                @error('bagian')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Grup -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="grup_id" class="form-label">Grup</label>
                <select name="grup_id" id="grup_id"
                        class="form-control @error('grup_id') is-invalid @enderror">
                    <option value="" selected>Pilih grup (opsional)</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->id }}" data-bagian="{{ $grup->bagian }}"
                            {{ old('grup_id') == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i> Pilih grup kerja user
                </small>
                @error('grup_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password" class="form-label">
                    Password <span class="text-danger">*</span>
                </label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Masukkan password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password <span class="text-danger">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control"
                       placeholder="Konfirmasi password" required>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary ml-2">Simpan</button>
    </div>
</form>
