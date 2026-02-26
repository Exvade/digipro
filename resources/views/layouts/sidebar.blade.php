<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <!-- Brand Logo -->
    @php
    $user = Auth::user();
    $dashboardRoute = route('menu'); // Default fallback to menu
    if ($user) {
    if ($user->role === 'leader') {
    $dashboardRoute = route('leader.dashboard.inject');
    } elseif ($user->role === 'super_admin') {
    $dashboardRoute = route('super-admin.dashboard.inject');
    } elseif ($user->role === 'kabag') {
    $dashboardRoute = route('kabag.dashboard.inject');
    } elseif ($user->role === 'manager_plant') {
    $dashboardRoute = route('manager-plant.dashboard.inject');
    } elseif ($user->role === 'staff') {
    $dashboardRoute = route('staff.dashboard.inject');
    }
    }

    $isRestrictedPloting = $user && in_array($user->role, ['super_admin', 'kabag']) && request()->routeIs('ploting.index');
    @endphp
    <a href="{{ $dashboardRoute }}" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        {{-- Hanya tampilkan isi sidebar jika BUKAN di halaman Dashboard Inject atau Dashboard Assy --}}
        @if (!request()->routeIs('*.dashboard.inject') && !request()->routeIs('*.dashboard.assy'))

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Mold Shop Section -->
                @if (!$isRestrictedPloting && $user && ($user->role !== 'super_admin' && (in_array($user->role, ['kabag', 'manager_plant']) || ($user->role === 'staff' && $user->departemen === 'Moldshop'))))
                <li class="nav-header">MOLD SHOP</li>
                <li class="nav-item">
                    <a href="{{ route('moldshop.index') }}" class="nav-link {{ request()->routeIs('moldshop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Data Mold</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('moldshop.history') }}" class="nav-link {{ request()->routeIs('moldshop.history') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Riwayat Mold</p>
                    </a>
                </li>
                @elseif (!$isRestrictedPloting && $user->role === 'super_admin')
                {{-- Only show history for super_admin in moldshop related sections if needed, but not in main sidebar master data --}}
                @endif

                <!-- Master Data Section -->
                @if (!$isRestrictedPloting && $user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']) || (in_array($user->role, ['leader', 'staff']) && $user->bagian != 'assy')))
                <li class="nav-header">MASTER DATA</li>

                @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                <li class="nav-item">
                    <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data User</p>
                    </a>
                </li>
                @if ($user->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('moldshop.index') }}" class="nav-link {{ request()->routeIs('moldshop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Data Mold</p>
                    </a>
                </li>
                @endif
                @endif

                <li class="nav-item">
                    <a href="{{ route('mesin.index') }}" class="nav-link {{ request()->routeIs('mesin.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-industry"></i>
                        <p>Data Mesin</p>
                    </a>
                </li>

                @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                <li class="nav-item">
                    <a href="{{ route('parts.index') }}" class="nav-link {{ request()->routeIs('parts.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Data Part</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('meja.index') }}" class="nav-link {{ request()->routeIs('meja.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>Data Meja</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>Data Dokumen</p>
                    </a>
                </li>
                @endif
                @endif


                <!-- Produksi Section -->
                @if ($user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                <li class="nav-header">PRODUKSI</li>

                @if (!$isRestrictedPloting && in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff']))
                <li class="nav-item {{ request()->routeIs('planning.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('planning.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>
                            Planning Produksi
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('planning.index') }}" class="nav-link {{ request()->routeIs('planning.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Planning</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('planning.history') }}" class="nav-link {{ request()->routeIs('planning.history') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Riwayat Planning</p>
                            </a>
                        </li>
                        @if($user->role == 'super_admin' || ($user->role == 'kabag' && ($user->departemen == 'PPIC' || $user->departemen == 'Production Planning Inventory Control')))
                        <li class="nav-item">
                            <a href="{{ route('planning.approval.page') }}" class="nav-link {{ request()->routeIs('planning.approval.page') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Approval Planning</p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif


                @if ($user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'foreman', 'leader']))
                <li class="nav-item">
                    <a href="{{ route('ploting.index') }}" class="nav-link {{ request()->routeIs('ploting.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Control Leader</p>
                    </a>
                </li>
                @endif

                @if (!$isRestrictedPloting && $user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                <li class="nav-item">
                    <a href="{{ route('jadwal-kerja.index') }}" class="nav-link {{ request()->routeIs('jadwal-kerja.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Jadwal Kerja</p>
                    </a>
                </li>
                @endif

                @if (!$isRestrictedPloting && $user && !in_array($user->role, ['staff', 'manager_plant']))
                <li class="nav-item">
                    <a href="{{ route('input-produksi.index') }}" class="nav-link {{ request()->routeIs('input-produksi.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-edit"></i>
                        <p>Input Hasil Produksi</p>
                    </a>
                </li>
                @endif

                @if (!$isRestrictedPloting && $user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                <li class="nav-item">
                    <a href="{{ route('oee.index') }}" class="nav-link {{ request()->routeIs('oee.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>OEE Monitoring</p>
                    </a>
                </li>
                @endif

                @if (!$isRestrictedPloting && $user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                <li class="nav-item">
                    <a href="{{ route('laporan-produksi.index') }}" class="nav-link {{ request()->routeIs('laporan-produksi.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Laporan Produksi</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('dashboard.inject.history') }}" class="nav-link {{ request()->routeIs('dashboard.inject.history') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Riwayat Mesin</p>
                    </a>
                </li>
                @endif
                @endif {{-- End of Produksi Section Check --}}

                <!-- Checksheet Section -->
                @if ($user && (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']) || ($user->role === 'staff' && $user->departemen !== 'Moldshop')))
                <li class="nav-item {{ request()->routeIs('checksheet.*') || request()->routeIs('approval.*') || request()->routeIs('report.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('checksheet.*') || request()->routeIs('approval.*') || request()->routeIs('report.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>
                            Checksheet
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('checksheet.index') }}" class="nav-link {{ request()->routeIs('checksheet.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Checksheet</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('approval.index') }}" class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Approval</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('report.index') }}" class="nav-link {{ request()->routeIs('report.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Report</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
            </ul>
        </nav>
        @endif
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>