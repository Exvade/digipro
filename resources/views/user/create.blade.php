<!-- This form is intended to be loaded via AJAX into a modal, not as a standalone page. -->
<div class="alert alert-success">
    <i class="fas fa-check-circle mr-2"></i>
    Form create user berhasil dimuat!
</div>

<form id="addForm" class="user-create-form" action="{{ route('user.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="username" class="form-label">
                    Username <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="username" 
                       id="username" 
                       value="{{ old('username') }}"
                       class="form-control"
                       placeholder="Masukkan username">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="nama" class="form-label">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="nama" 
                       id="nama" 
                       value="{{ old('nama') }}"
                       class="form-control"
                       placeholder="Masukkan nama lengkap">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}"
                       class="form-control"
                       placeholder="Masukkan email">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="role" class="form-label">
                    Role <span class="text-danger">*</span>
                </label>
                <select name="role" 
                        id="role" 
                        class="form-control">
                    <option value="">Pilih role</option>
                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="leader" {{ old('role') == 'leader' ? 'selected' : '' }}>Leader</option>
                    <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="foreman" {{ old('role') == 'foreman' ? 'selected' : '' }}>Foreman</option>
                    <option value="kabag" {{ old('role') == 'kabag' ? 'selected' : '' }}>Kabag</option>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager_plant" {{ old('role') == 'manager_plant' ? 'selected' : '' }}>Manager Plant</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="bagian" class="form-label">
                    Bagian
                </label>
                <select name="bagian" 
                        id="bagian" 
                        class="form-control">
                    <option value="" selected>Pilih bagian (opsional)</option>
                    <option value="inject" {{ old('bagian') == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ old('bagian') == 'assy' ? 'selected' : '' }}>Assy</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="grup_id" class="form-label">
                    Grup
                </label>
                <select name="grup_id" id="grup_id" class="form-control">
                    <option value="" selected>Pilih grup (opsional)</option>
                    @foreach($grups as $grup)
                        <option value="{{ $grup->id }}" 
                                data-bagian="{{ $grup->bagian }}"
                                {{ old('grup_id') == $grup->id ? 'selected' : '' }}>
                            {{ $grup->nama_grup }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Pilih grup kerja user
                </small>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="password" class="form-label">
                    Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       class="form-control"
                       placeholder="Masukkan password">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation" 
                       class="form-control"
                       placeholder="Konfirmasi password">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" 
                class="btn btn-secondary" 
                data-dismiss="modal">
            <i class="fas fa-times me-2"></i>Batal
        </button>
        <button type="submit" 
                class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Simpan
        </button>
    </div>
</form>

<!-- Include CSS and JS files -->
<link rel="stylesheet" href="{{ asset('css/user-create.css') }}">
<script src="{{ asset('js/user-create.js') }}"></script>
