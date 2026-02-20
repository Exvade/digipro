@php
$hideSidebar = !Auth::check() || request()->routeIs('*.dashboard.inject') || request()->routeIs('*.dashboard.assy') || request()->routeIs('dashboard.inject.public');
@endphp
<nav class="main-header navbar navbar-expand navbar-white navbar-light dashboard-header-custom {{ $hideSidebar ? 'ml-0' : '' }}">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        @if(!$hideSidebar)
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        @endif

        <!-- Logo and Dashboard Title -->
        @if($hideSidebar)
        <li class="nav-item d-flex align-items-center">
            <img src="{{ asset('images/logo bulat.png') }}" alt="Logo" class="header-logo-inject ml-2">
            <div class="ml-2 header-text-container">
                <div class="header-title-inject">
                    @if(Auth::check() && Auth::user()->role == 'leader')
                    Andon System PT. Mada Wikri Tunggal 
                    @else
                    Dashboard Monitoring Produksi
                    @endif
                </div>
                <div class="header-subtitle-inject">Plant 3 - Plastic Injection </div>
            </div>
        </li>
        @endif
    </ul>

    <!-- Center: Realtime Clock (Responsive) -->
    @if($hideSidebar)
    <div class="navbar-nav mx-auto d-flex align-items-center">
        <div class="header-datetime-inject">
            <div class="datetime-box">
                <div class="datetime-item date-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="realtimeDate">-</span>
                </div>
                <div class="datetime-divider d-none d-md-block"></div>
                <div class="datetime-item time-item">
                    <i class="fas fa-clock"></i>
                    <span id="realtimeTime">-</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Dark Mode Toggle Switch -->
        <li class="nav-item d-flex align-items-center mr-2">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="darkModeSwitch">
                <label class="custom-control-label" for="darkModeSwitch">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </label>
            </div>
        </li>
        <li class="nav-item dropdown d-none d-md-block">
            <a class="nav-link nav-notif-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge badge-warning navbar-badge"></span>
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

        <!-- User Profile Dropdown -->
        @auth
        <li class="nav-item dropdown">
            <a href="#" class="nav-link d-flex align-items-center" data-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=2e59d9&color=fff&bold=true"
                    class="img-circle elevation-1 mr-2" style="width: 30px; height: 30px; object-fit: cover;" alt="User">
                <span class="d-none d-md-inline font-weight-bold ml-1 text-dark">{{ Auth::user()->nama }}</span>
                <i class="fas fa-angle-down ml-2 d-none d-md-inline opacity-50" style="font-size: 0.8rem;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-1" style="min-width: 170px; border-radius: 12px; margin-top: 10px; overflow: hidden;">
                <a href="{{ route('menu') }}" class="dropdown-item py-2 px-3 d-flex align-items-center">
                    <i class="fas fa-home text-primary mr-2" style="width: 20px;"></i>
                    <span style="font-size: 0.95rem;">Menu Utama</span>
                </a>
                <div class="dropdown-divider my-1"></div>
                <a href="#" class="dropdown-item py-2 px-3 text-danger d-flex align-items-center"
                    onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                    <i class="fas fa-sign-out-alt text-danger mr-2" style="width: 20px;"></i>
                    <strong style="font-size: 0.95rem;">Logout</strong>
                </a>
                <form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
        @else
        <li class="nav-item">
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-sign-in-alt mr-1"></i> Login
            </a>
        </li>
        @endauth
    </ul>
</nav>