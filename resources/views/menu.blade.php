<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Menu - Digital Production System</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/menu.css') }}" rel="stylesheet">
</head>

<body class="menu-page">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-overlay"></div>
        <div class="geometric-pattern"></div>
    </div>

    <div class="content-wrapper">
        <!-- Dark Mode Toggle Switch -->
        <div class="dark-mode-toggle-wrapper">
            <label class="toggle-switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="toggle-slider">
                    <i class="fas fa-moon toggle-icon moon"></i>
                    <i class="fas fa-sun toggle-icon sun"></i>
                </span>
            </label>
        </div>

        <div class="container mt-4 mb-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="menu-card">
                        <div class="welcome-section">
                            <div class="profile-avatar">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama ?? 'User') }}&background=2e59d9&color=fff&size=80"
                                    alt="Avatar">
                            </div>
                            @php
                            $hour = date('H');
                            if ($hour >= 5 && $hour < 11) {
                                $greeting='Selamat Pagi' ;
                                } elseif ($hour>= 11 && $hour < 15) {
                                    $greeting='Selamat Siang' ;
                                    } elseif ($hour>= 15 && $hour < 18) {
                                        $greeting='Selamat Sore' ;
                                        } else {
                                        $greeting='Selamat Malam' ;
                                        }
                                        @endphp
                                        <div class="welcome-text">{{ $greeting }},
                        </div>
                        <div class="text-primary">{{ ucfirst(Auth::user()->nama) }}</div>
                    </div>

                    <div class="menu-buttons">
                        @php
                        $user = auth()->user();
                        $isPPICRole = in_array($user->role, ['kabag', 'staff']) &&
                        (in_array($user->departemen, ['PPIC', 'Production Planning Inventory Control', 'Production Planning & Inventory Control']));

                        // For legacy checks if still needed elsewhere
                        $isKabagPPIC = $user->role == 'kabag' && $isPPICRole;
                        @endphp

                        @if(!$isPPICRole)
                        {{-- Master Data --}}
                        @if (
                        in_array($user->role, ['super_admin', 'kabag', 'manager_plant']) ||
                        ($user->role == 'staff' && $user->departemen == 'Moldshop') ||
                        ($user->role == 'leader' && $user->bagian == 'assy')
                        )
                        @php
                        $masterRoute = route('user.index');
                        if ($user->role == 'staff' && $user->departemen == 'Moldshop') {
                        $masterRoute = route('moldshop.index');
                        } elseif ($user->role == 'leader' && $user->bagian == 'assy') {
                        $masterRoute = route('meja.index');
                        }
                        @endphp
                        <a href="{{ $masterRoute }}" class="menu-btn master-btn">
                            <i class="fas fa-database"></i> Master Data
                        </a>
                        @endif

                        {{-- Data Mesin --}}
                        @if (
                        in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader']) &&
                        !($user->role == 'leader' && $user->bagian == 'assy')
                        )
                        <a href="{{ route('mesin.index') }}" class="menu-btn machine-btn">
                            <i class="fas fa-industry"></i> Data Mesin
                        </a>
                        @endif

                        {{-- Jadwal Pemeliharaan Mesin --}}
                        @if (
                        in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader']) &&
                        !($user->role == 'leader' && $user->bagian == 'assy')
                        )
                        <a href="{{ route('jadwal-pemeliharaan.index') }}" class="menu-btn" style="background-image: linear-gradient(135deg, #1cc88a, #13855c); color: white;">
                            <i class="fas fa-tools"></i> Jadwal Pemeliharaan
                        </a>
                        @endif

                        {{-- Jadwal Kerja --}}
                        @if (in_array($user->role, [
                        'super_admin',
                        'kabag',
                        'manager_plant',
                        'staff',
                        'leader',
                        'foreman',
                        'operator',
                        ]) && (in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant']) || in_array($user->bagian, ['inject', 'assy'])))
                        <a href="{{ route('jadwal-kerja.index') }}" class="menu-btn schedule-btn">
                            <i class="fas fa-calendar-alt"></i> Jadwal Kerja
                        </a>
                        @endif
                        @endif

                        {{-- Planning Produksi --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'staff', 'manager_plant']) &&
                        in_array($user->departemen, [
                        'Production Planning Inventory Control',
                        'Information Technology'
                        ]))
                        <a href="{{ route('planning.index') }}" class="menu-btn planning-btn">
                            <i class="fas fa-calendar-check"></i> Planning Produksi
                        </a>
                        @endif

                        {{-- Dashboard Section --}}
                        @php
                        $showAssy = false;
                        $showInject = false;
                        $assyRoute = '#';
                        $injectRoute = '#';

                        // Logic for Admin, Kabag, Manager Plant, Staff, and Foreman
                        if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'foreman']) &&
                        (in_array($user->departemen, ['Production Planning Inventory Control', 'Information Technology', 'Manager Plant', 'Produksi'])) && !$isPPICRole) {
                        $showAssy = true;
                        $showInject = true;

                        if ($user->role === 'super_admin') {
                        $assyRoute = route('super-admin.dashboard.assy');
                        $injectRoute = route('super-admin.dashboard.inject');
                        } elseif ($user->role === 'kabag') {
                        $assyRoute = route('kabag.dashboard.assy');
                        $injectRoute = route('kabag.dashboard.inject');
                        } elseif ($user->role === 'manager_plant') {
                        $assyRoute = route('manager-plant.dashboard.assy');
                        $injectRoute = route('manager-plant.dashboard.inject');
                        } elseif ($user->role === 'staff') {
                        $assyRoute = route('staff.dashboard.assy');
                        $injectRoute = route('staff.dashboard.inject');
                        } elseif ($user->role === 'foreman') {
                        $assyRoute = route('foreman.dashboard.assy');
                        $injectRoute = route('foreman.dashboard.inject');
                        } else {
                        $assyRoute = route('leader.dashboard.assy');
                        $injectRoute = route('leader.dashboard.inject');
                        }
                        }
                        // Logic for Leader based on Bagian
                        elseif ($user->role === 'leader') {
                        if ($user->bagian === 'assy') {
                        $showAssy = true;
                        $assyRoute = route('leader.dashboard.assy');
                        } elseif ($user->bagian === 'inject') {
                        $showInject = true;
                        $injectRoute = route('leader.dashboard.inject');
                        }
                        }
                        @endphp

                        @if($showAssy)
                        <a href="{{ $assyRoute }}" class="menu-btn dashboard-assy-btn">
                            <i class="fas fa-cogs"></i> Dashboard Assy
                        </a>
                        @endif

                        @if($showInject)
                        <a href="{{ $injectRoute }}" class="menu-btn dashboard-inject-btn">
                            <i class="fas fa-cubes"></i> Dashboard Inject
                        </a>
                        @endif

                        @if(!$isPPICRole)
                        {{-- Control Leader --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'foreman', 'leader']))
                        <a href="{{ route('ploting.index') }}" class="menu-btn ploting-btn">
                            <i class="fas fa-users-cog"></i> Control Leader
                        </a>
                        @endif
                        @endif

                        {{-- Input Hasil Produksi --}}
                        @if (!in_array($user->role, ['staff', 'manager_plant']) && !$isPPICRole)
                        <a href="{{ route('input-produksi.index') }}" class="menu-btn production-btn">
                            <i class="fas fa-clipboard-list"></i> Input Hasil Produksi
                        </a>
                        @endif

                        {{-- OEE --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff']) &&
                        in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant']) && !$isPPICRole)
                        <a href="{{ route('oee.index') }}" class="menu-btn oee-btn">
                            <i class="fas fa-chart-line"></i> Overall Equipment Effectiveness
                        </a>
                        @endif

                        {{-- Laporan Produksi --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff' , 'leader', 'foreman']) &&
                        in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant']) && !$isPPICRole)
                        <a href="{{ route('laporan-produksi.index') }}" class="menu-btn report-btn">
                            <i class="fas fa-file-alt"></i> Laporan Produksi
                        </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="menu-btn logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <footer class="footer mt-auto py-4 bg-white">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} All rights reserved | IT Department PT Mada Wikri Tunggal</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/menu-dark-mode.js') }}"></script>
</body>

</html>