@extends('oee.layouts.app')

@section('title', 'Kode Downtime')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Kode Downtime
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('oee.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kode Downtime</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $downtimeCodes->count() }}</h3>
                        <p>Total Kode</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $downtimeCodes->where('type', 'planned')->count() }}</h3>
                        <p>Planned Downtime</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $downtimeCodes->where('type', 'unplanned')->count() }}</h3>
                        <p>Unplanned Downtime</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $downtimeCodes->where('requires_approval', true)->count() }}</h3>
                        <p>Perlu Approval</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-2"></i>Kelola Kode Downtime
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCodeModal">
                                <i class="fas fa-plus mr-1"></i> Tambah Kode
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="exportDowntimeCodes()">
                                <i class="fas fa-download mr-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Total {{ $downtimeCodes->count() }} kode downtime terdaftar dalam sistem</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Downtime Codes Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Daftar Kode Downtime
                        </h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="table_search" class="form-control float-right" placeholder="Cari kode...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Tipe</th>
                                    <th>Tanggung Jawab</th>
                                    <th>Status</th>
                                    <th>Urutan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($downtimeCodes as $code)
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary">{{ $code->code }}</span>
                                    </td>
                                    <td>{{ $code->name }}</td>
                                    <td>
                                        @if($code->category)
                                        <span class="badge" style="background-color: {{ $code->category->color }}; color: white;">
                                            {{ $code->category->name }}
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $code->type === 'planned' ? 'warning' : 'danger' }}">
                                            {{ ucfirst($code->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($code->responsibility) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $code->is_active ? 'success' : 'secondary' }}">
                                            {{ $code->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>{{ $code->sort_order }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" onclick="viewCodeDetails({{ $code->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning" onclick="editCode({{ $code->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteCode({{ $code->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-exclamation-triangle text-muted fa-2x mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada kode downtime terdaftar</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Code Modal -->
<div class="modal fade" id="addCodeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Tambah Kode Downtime
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addCodeForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-tag mr-1"></i>Kode
                                </label>
                                <input type="text" class="form-control" name="code" placeholder="Contoh: EF001" required>
                                <div class="invalid-feedback">
                                    Kode harus diisi
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-font mr-1"></i>Nama
                                </label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan nama kode" required>
                                <div class="invalid-feedback">
                                    Nama harus diisi
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder mr-1"></i>Kategori
                                </label>
                                <select class="form-control" name="downtime_category_id" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach(\App\Models\DowntimeCategory::active()->ordered()->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Kategori harus dipilih
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-clock mr-1"></i>Tipe
                                </label>
                                <select class="form-control" name="type" required>
                                    <option value="">Pilih tipe</option>
                                    <option value="planned">Planned</option>
                                    <option value="unplanned">Unplanned</option>
                                </select>
                                <div class="invalid-feedback">
                                    Tipe harus dipilih
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user mr-1"></i>Tanggung Jawab
                                </label>
                                <select class="form-control" name="responsibility" required>
                                    <option value="">Pilih tanggung jawab</option>
                                    <option value="operator">Operator</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="external">External</option>
                                </select>
                                <div class="invalid-feedback">
                                    Tanggung jawab harus dipilih
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>Urutan
                                </label>
                                <input type="number" class="form-control" name="sort_order" placeholder="Masukkan urutan" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left mr-1"></i>Deskripsi
                        </label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Masukkan deskripsi kode downtime"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="requires_approval" name="requires_approval" value="1">
                            <label class="custom-control-label" for="requires_approval">
                                Memerlukan approval
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="addCodeBtn">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Form validation
    $('#addCodeForm').on('submit', function(e) {
        e.preventDefault();
        
        if (this.checkValidity() === false) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        
        // Submit form via AJAX
        $.ajax({
            url: '/oee/settings/downtime-codes',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addCodeModal').modal('hide');
                    location.reload();
                } else {
                    alert('Gagal menambahkan kode downtime');
                }
            },
            error: function() {
                alert('Terjadi kesalahan');
            }
        });
    });
});

function viewCodeDetails(codeId) {
    // Implementation for viewing code details
    alert('View details for code ID: ' + codeId);
}

function editCode(codeId) {
    // Implementation for editing code
    alert('Edit code ID: ' + codeId);
}

function deleteCode(codeId) {
    if (confirm('Apakah Anda yakin ingin menghapus kode downtime ini?')) {
        $.ajax({
            url: '/oee/settings/downtime-codes/' + codeId,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus kode downtime');
                }
            },
            error: function() {
                alert('Terjadi kesalahan');
            }
        });
    }
}

function exportDowntimeCodes() {
    window.location.href = '/oee/settings/downtime-codes/export';
}
</script>
@endpush
@endsection 