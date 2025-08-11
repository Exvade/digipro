<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <!-- Brand Logo -->
    @php
        $user = Auth::user();
        $dashboardRoute = route('menu'); // Default fallback to menu
        if ($user->role === 'leader') {
            $dashboardRoute = route('leader.dashboard.inject');
        } elseif ($user->role === 'super_admin') {
            $dashboardRoute = route('super-admin.dashboard.inject');
        } elseif ($user->role === 'kabag') {
            $dashboardRoute = route('kabag.dashboard.inject');
        } elseif ($user->role === 'manager_plant') {
            $dashboardRoute = route('manager-plant.dashboard.inject');
        }
    @endphp
    <a href="{{ $dashboardRoute }}" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info d-none d-sm-block">
                <a href="#" class="d-block">{{ $user->nama }}</a>
            </div>
            <div class="info d-sm-none">
                <a href="#" class="d-block">{{ $user->nama }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                
                <!-- Dashboard Section -->
                @if(in_array($user->role, ['super_admin', 'leader', 'kabag', 'manager_plant', 'foreman']))
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-expanded="false">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            <span class="d-none d-sm-inline">Dashboard</span>
                            <span class="d-sm-none">Dashboard</span>
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($user->role === 'super_admin')
                        <li class="nav-item">
                            <a href="{{ route('super-admin.dashboard.inject') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard.inject') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Inject</span>
                                    <span class="d-sm-none">Dashboard Inject</span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('super-admin.dashboard.assy') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard.assy') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Assy</span>
                                    <span class="d-sm-none">Dashboard Assy</span>
                                </p>
                            </a>
                        </li>
                        @elseif($user->role === 'leader')
                            @if(isset($user->bagian) && strtolower($user->bagian) === 'inject')
                            <li class="nav-item">
                                <a href="{{ route('leader.dashboard.inject') }}" class="nav-link {{ request()->routeIs('leader.dashboard.inject') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>
                                        <span class="d-none d-sm-inline">Dashboard Inject</span>
                                        <span class="d-sm-none">Dashboard Inject</span>
                                    </p>
                                </a>
                            </li>
                            @endif
                            @if(isset($user->bagian) && strtolower($user->bagian) === 'assy')
                            <li class="nav-item">
                                <a href="{{ route('leader.dashboard.assy') }}" class="nav-link {{ request()->routeIs('leader.dashboard.assy') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>
                                        <span class="d-none d-sm-inline">Dashboard Assy</span>
                                        <span class="d-sm-none">Dashboard Assy</span>
                                    </p>
                                </a>
                            </li>
                            @endif
                        @elseif($user->role === 'kabag')
                        <li class="nav-item">
                            <a href="{{ route('kabag.dashboard.inject') }}" class="nav-link {{ request()->routeIs('kabag.dashboard.inject') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Inject</span>
                                    <span class="d-sm-none">Dashboard Inject</span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('kabag.dashboard.assy') }}" class="nav-link {{ request()->routeIs('kabag.dashboard.assy') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Assy</span>
                                    <span class="d-sm-none">Dashboard Assy</span>
                                </p>
                            </a>
                        </li>
                        @elseif($user->role === 'manager_plant')
                        <li class="nav-item">
                            <a href="{{ route('manager-plant.dashboard.inject') }}" class="nav-link {{ request()->routeIs('manager-plant.dashboard.inject') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Inject</span>
                                    <span class="d-sm-none">Dashboard Inject</span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager-plant.dashboard.assy') }}" class="nav-link {{ request()->routeIs('manager-plant.dashboard.assy') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Assy</span>
                                    <span class="d-sm-none">Dashboard Assy</span>
                                </p>
                            </a>
                        </li>
                        @elseif($user->role === 'foreman')
                        <li class="nav-item">
                            <a href="{{ route('foreman.dashboard.inject') }}" class="nav-link {{ request()->routeIs('foreman.dashboard.inject') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Inject</span>
                                    <span class="d-sm-none">Dashboard Inject</span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('foreman.dashboard.assy') }}" class="nav-link {{ request()->routeIs('foreman.dashboard.assy') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>
                                    <span class="d-none d-sm-inline">Dashboard Assy</span>
                                    <span class="d-sm-none">Dashboard Assy</span>
                                </p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Master Data Section -->
                @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-expanded="false">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            <span class="d-none d-sm-inline">Master Data</span>
                            <span class="d-sm-none">Data</span>
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($user->role === 'super_admin')
                        <li class="nav-item">
                            <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User</p>
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                            <a href="{{ route('mesin.index') }}" class="nav-link {{ request()->routeIs('mesin.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Mesin</p>
                            </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                            <a href="{{ route('parts.index') }}" class="nav-link {{ request()->routeIs('parts.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Part</p>
                            </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                            <a href="{{ route('meja.index') }}" class="nav-link {{ request()->routeIs('meja.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Meja</p>
                            </a>
                            @endif
                        </li>
                    </ul>
                </li>
                @endif

                <!-- Checksheet Section -->
                @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']))
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-expanded="false">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>
                            <span class="d-none d-sm-inline">Checksheet</span>
                            <span class="d-sm-none">Check</span>
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                            <a href="{{ route('checksheet.index') }}" class="nav-link {{ request()->routeIs('checksheet.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Checksheet</p>
                            </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                            <a href="{{ route('approval.index') }}" class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Approval</p>
                            </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            @if(in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman']))
                            <a href="{{ route('report.index') }}" class="nav-link {{ request()->routeIs('report.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Report</p>
                            </a>
                            @endif
                        </li>
                    </ul>
                </li>
                @endif

                <!-- Logout -->
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            <span class="d-none d-sm-inline">Logout</span>
                            <span class="d-sm-none">Logout</span>
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside> 