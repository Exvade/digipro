<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OEE System - @yield('title', 'Dashboard')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <!-- OEE Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/oee.css') }}">
    
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('oee.dashboard.index') }}" class="nav-link">Dashboard</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend requests
                            <span class="float-right text-muted text-sm">12 hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">User Menu</span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="dropdown-item">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-left w-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('oee.dashboard.index') }}" class="brand-link">
                <i class="fas fa-cogs brand-image img-circle elevation-3" style="opacity: .8"></i>
                <span class="brand-text font-weight-light">OEE System</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('oee.dashboard.index') }}" class="nav-link {{ request()->routeIs('oee.dashboard.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard OEE</p>
                            </a>
                        </li>

                        <!-- Production Planning -->
                        <li class="nav-item {{ request()->routeIs('oee.planning.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('oee.planning.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>
                                    Production Planning
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('oee.planning.index') }}" class="nav-link {{ request()->routeIs('oee.planning.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Planning List</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.planning.create') }}" class="nav-link {{ request()->routeIs('oee.planning.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Add New Planning</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.planning.schedule') }}" class="nav-link {{ request()->routeIs('oee.planning.schedule') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Production Schedule</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.planning.analysis') }}" class="nav-link {{ request()->routeIs('oee.planning.analysis') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Planning Analysis</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Production Data -->
                        <li class="nav-item {{ request()->routeIs('oee.production.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('oee.production.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-industry"></i>
                                <p>
                                    Data Produksi
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('oee.production.downtime') }}" class="nav-link {{ request()->routeIs('oee.production.downtime') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Downtime Analysis</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.production.quality') }}" class="nav-link {{ request()->routeIs('oee.production.quality') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Quality Analysis</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.production.performance') }}" class="nav-link {{ request()->routeIs('oee.production.performance') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Performance Analysis</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.production.shifts') }}" class="nav-link {{ request()->routeIs('oee.production.shifts') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Shift Analysis</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item {{ request()->routeIs('oee.settings.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('oee.settings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>
                                    Pengaturan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('oee.settings.targets') }}" class="nav-link {{ request()->routeIs('oee.settings.targets') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Target OEE</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('oee.settings.downtime-codes') }}" class="nav-link {{ request()->routeIs('oee.settings.downtime-codes') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kode Downtime</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Back to Menu -->
                        <li class="nav-item">
                            <a href="{{ route('menu') }}" class="nav-link">
                                <i class="nav-icon fas fa-arrow-left"></i>
                                <p>Kembali ke Menu</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                OEE System v1.0
            </div>
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Digital Production</a>.</strong> All rights reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    
    <!-- OEE Custom JavaScript -->
    <script src="{{ asset('js/oee.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
