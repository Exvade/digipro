<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/super-admin/dashboard/inject') }}" class="nav-link">Dashboard</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown d-none d-md-block">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="far fa-bell"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">Tidak ada notifikasi</span>
            </div>
        </li>
        
        <!-- Mobile notification icon -->
        <li class="nav-item d-md-none">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#mobileNotificationsModal" aria-label="Notifications">
                <i class="far fa-bell"></i>
            </a>
        </li>
        
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Fullscreen">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" aria-label="Control Sidebar">
                <i class="fas fa-th-large"></i>
            </a>
        </li>
    </ul>
</nav> 