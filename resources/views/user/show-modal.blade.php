<div class="user-detail-container">
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3 pb-2 border-bottom">
                <i class="fas fa-info-circle mr-2 text-primary"></i>Informasi Akun
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>NIK</th>
                        <td>{{ $user->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td class="font-weight-bold">{{ $user->nama }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            @php
                            $roleBadge = [
                            'super_admin' => 'danger',
                            'leader' => 'primary',
                            'operator' => 'info',
                            'foreman' => 'warning',
                            'kabag' => 'secondary',
                            'staff' => 'light',
                            'manager_plant' => 'dark'
                            ];
                            $badgeClass = $roleBadge[$user->role] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badgeClass }} px-2 py-1">
                                {{ strtoupper(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Departemen</th>
                        <td>{{ $user->departemen ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Bagian</th>
                        <td>
                            @if($user->bagian)
                            <span class="badge badge-{{ $user->bagian == 'inject' ? 'success' : 'info' }}">
                                {{ strtoupper($user->bagian) }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Grup</th>
                        <td>
                            @if($user->grup_id && $user->grup)
                            <span class="badge badge-secondary">{{ $user->grup->nama_grup }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status Akun</th>
                        <td>
                            @if($user->is_active)
                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>AKTIF</span>
                            @else
                            <span class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i>NONAKTIF</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <h5 class="mb-3 pb-2 border-bottom">
                <i class="fas fa-history mr-2 text-info"></i>Log Aktivitas
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>Terdaftar Pada</th>
                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Login</th>
                        <td>
                            @if($user->last_login_at)
                            {{ $user->last_login_at->format('d/m/Y H:i') }}
                            <small class="text-muted ml-2">({{ $user->last_login_at->diffForHumans() }})</small>
                            @else
                            <span class="text-muted italic">Belum pernah login</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>