@extends('layouts.app')

@section('title', 'Data User')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Custom User CSS -->
<link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endpush

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <h3 class="card-title mb-2 mb-md-0">
                        <i class="fas fa-users me-2"></i>
                        Data User
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm w-100 w-md-auto" onclick="openCreateModal()">
                            <i class="fas fa-plus me-1"></i>
                            <span class="d-none d-sm-inline">Tambah User</span>
                            <span class="d-inline d-sm-none">Tambah</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <i class="icon fas fa-check me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <i class="icon fas fa-ban me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <!-- Grup Statistics -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                                            <h6 class="mb-0 text-primary">{{ $users->count() }}</h6>
                                            <small class="text-muted d-block d-md-inline">Total User</small>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                                            <h6 class="mb-0 text-success">{{ $users->where('grup_id', '!=', null)->count() }}</h6>
                                            <small class="text-muted d-block d-md-inline">User dengan Grup</small>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                                            <h6 class="mb-0 text-warning">{{ $users->where('grup_id', null)->count() }}</h6>
                                            <small class="text-muted d-block d-md-inline">User tanpa Grup</small>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <h6 class="mb-0 text-info">{{ $users->pluck('grup_id')->unique()->filter()->count() }}</h6>
                                            <small class="text-muted d-block d-md-inline">Jumlah Grup Aktif</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="userTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Role</th>
                                    <th class="d-none d-lg-table-cell">Bagian</th>
                                    <th class="d-none d-lg-table-cell">Grup</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $user->username }}</span>
                                                <small class="text-muted d-md-none">{{ $user->email }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $user->nama }}</td>
                                        <td class="d-none d-md-table-cell">{{ $user->email }}</td>
                                        <td>
                                            @switch($user->role)
                                                @case('super_admin')
                                                    <span class="badge badge-danger">Super Admin</span>
                                                    @break
                                                @case('leader')
                                                    <span class="badge badge-primary">Leader</span>
                                                    @break
                                                @case('operator')
                                                    <span class="badge badge-info">Operator</span>
                                                    @break
                                                @case('foreman')
                                                    <span class="badge badge-warning">Foreman</span>
                                                    @break
                                                @case('kabag')
                                                    <span class="badge badge-secondary">Kabag</span>
                                                    @break
                                                @case('staff')
                                                    <span class="badge badge-light">Staff</span>
                                                    @break
                                                @case('manager_plant')
                                                    <span class="badge badge-dark">Manager Plant</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($user->role) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($user->bagian)
                                                @if($user->bagian == 'inject')
                                                    <span class="badge badge-success">Inject</span>
                                                @elseif($user->bagian == 'assy')
                                                    <span class="badge badge-info">Assy</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($user->bagian) }}</span>
                                                @endif
                                            @else
                                                <span class="badge badge-light">-</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($user->grup_id)
                                                <span class="badge badge-secondary">Grup {{ $user->grup->nama }}</span>
                                            @else
                                                <span class="badge badge-light">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        title="Edit"
                                                        onclick="openEditModal({{ $user->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($user->id !== auth()->id())
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Hapus"
                                                            onclick="deleteUser({{ $user->id }}, '{{ $user->nama }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">
                    <i class="fas fa-plus me-2"></i>Tambah User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addModalBody">
                <!-- Form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editModalBody">
                <!-- Form will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom User JS -->
<script src="{{ asset('js/user.js') }}"></script>

<script>
    // Make route URLs available to JavaScript
    window.createModalUrl = '{{ route("user.create") }}';
</script>
@endpush
