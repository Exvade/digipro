<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Left side - Title -->

        <!-- Right side - User Profile Dropdown -->
        <div class="navbar-nav ml-auto align-items-center">
            <!-- Dark Mode Toggle -->
            <div class="nav-item mr-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="darkModeSwitch">
                    <label class="custom-control-label" for="darkModeSwitch">
                        <i class="fas fa-moon text-white" id="darkModeIcon"></i>
                    </label>
                </div>
            </div>

            <!-- Notifications Dropdown Menu -->
            <div class="nav-item dropdown mr-2">
                <a class="nav-link nav-notif-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="fas fa-bell text-white" style="font-size: 1rem;"></i>
                    <span class="badge badge-warning navbar-badge" style="font-size: 0.6rem; position: absolute; top: -5px; right: -5px;"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width: 280px;">
                    <span class="dropdown-item dropdown-header">Tidak ada notifikasi</span>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            @auth
            <div class="nav-item dropdown ml-2">
                <a href="#" class="nav-link d-flex align-items-center py-1 px-1" data-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=ffffff&color=007bff&bold=true"
                        class="rounded-circle shadow-sm" style="width: 30px; height: 30px; object-fit: cover;" alt="User">
                    <span class="d-none d-md-inline font-weight-bold ml-1 text-white" style="font-size: 0.9rem;">{{ Auth::user()->nama }}</span>
                    <i class="fas fa-angle-down ml-1 d-none d-md-inline text-white opacity-75" style="font-size: 0.7rem;"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-1" style="min-width: 170px; border-radius: 12px; margin-top: 10px; overflow: hidden;">
                    <a href="{{ route('menu') }}" class="dropdown-item py-2 px-3 d-flex align-items-center">
                        <i class="fas fa-home text-primary mr-2" style="width: 20px;"></i>
                        <span style="font-size: 0.95rem;">Menu Utama</span>
                    </a>
                    <a href="{{ route('oee.index') }}" class="dropdown-item py-2 px-3 d-flex align-items-center">
                        <i class="fas fa-chart-line text-success mr-2" style="width: 20px;"></i>
                        <span style="font-size: 0.95rem;">OEE Dashboard</span>
                    </a>
                    <div class="dropdown-divider my-1"></div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 px-3 text-danger d-flex align-items-center border-0 bg-transparent w-100">
                            <i class="fas fa-sign-out-alt text-danger mr-2" style="width: 20px;"></i>
                            <strong style="font-size: 0.95rem;">Logout</strong>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>