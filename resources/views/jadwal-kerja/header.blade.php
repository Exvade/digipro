<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <!-- Sidebar Toggle -->
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Dark Mode Toggle Switch - Smooth Slide -->
        <li class="nav-item d-flex align-items-center mr-2">
            <label class="toggle-switch-modern">
                <input type="checkbox" id="darkModeSwitch">
                <span class="toggle-slider-modern">
                    <i class="fas fa-moon toggle-icon-moon"></i>
                    <i class="fas fa-sun toggle-icon-sun"></i>
                </span>
            </label>
        </li>

        <style>
            /* Modern Toggle Switch */
            .toggle-switch-modern {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 30px;
                margin: 0;
                cursor: pointer;
            }

            .toggle-switch-modern input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .toggle-slider-modern {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 30px;
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            }

            .toggle-slider-modern:before {
                position: absolute;
                content: "";
                height: 22px;
                width: 22px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 50%;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            }

            /* Icons */
            .toggle-icon-moon,
            .toggle-icon-sun {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                font-size: 12px;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1;
            }

            .toggle-icon-moon {
                left: 8px;
                color: #ffffff;
                opacity: 1;
            }

            .toggle-icon-sun {
                right: 8px;
                color: #fbbf24;
                opacity: 0;
            }

            /* Checked state */
            input:checked+.toggle-slider-modern {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                box-shadow: 0 2px 8px rgba(30, 41, 59, 0.4);
            }

            input:checked+.toggle-slider-modern:before {
                transform: translateX(30px);
            }

            input:checked+.toggle-slider-modern .toggle-icon-moon {
                opacity: 0;
            }

            input:checked+.toggle-slider-modern .toggle-icon-sun {
                opacity: 1;
            }

            /* Hover effect */
            .toggle-slider-modern:hover {
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }

            input:checked+.toggle-slider-modern:hover {
                box-shadow: 0 4px 12px rgba(30, 41, 59, 0.5);
            }

            /* Active/Click effect */
            .toggle-slider-modern:active:before {
                width: 26px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .toggle-switch-modern {
                    width: 50px;
                    height: 26px;
                }

                .toggle-slider-modern:before {
                    height: 18px;
                    width: 18px;
                }

                input:checked+.toggle-slider-modern:before {
                    transform: translateX(24px);
                }

                .toggle-icon-moon,
                .toggle-icon-sun {
                    font-size: 10px;
                }
            }
        </style>

        <!-- Notifications Dropdown (Desktop) -->
        <li class="nav-item dropdown d-none d-md-block">
            <a class="nav-link nav-notif-link" data-toggle="dropdown" href="#" aria-expanded="false" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="badge badge-warning navbar-badge"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">Tidak ada notifikasi</span>
            </div>
        </li>

        <!-- Notifications Icon (Mobile) -->
        <li class="nav-item d-md-none">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#mobileNotificationsModal" aria-label="Notifications">
                <i class="far fa-bell"></i>
            </a>
        </li>

        <!-- Fullscreen -->
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
        @endauth
    </ul>
</nav>