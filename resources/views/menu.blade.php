<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Menu - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/menu.css') }}" rel="stylesheet">
</head>
<body class="menu-page">
    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="menu-card">
                    <div class="welcome-section">
                        <div class="profile-avatar">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama ?? 'User') }}&background=2e59d9&color=fff&size=80" alt="Avatar">
                        </div>
                        <div class="welcome-text">Selamat Datang,</div>
                        <div class="text-primary">
                            {{ ucfirst(Auth::user()->nama) }} - {{ ucfirst(Auth::user()->role) }}
                        </div>
                    </div>

                    <div class="menu-buttons">
                        @if(in_array(auth()->user()->role, ['super_admin', 'kabag', 'manager_plant', 'staff']))
                            <a href="{{ route('oee.dashboard.index') }}" class="menu-btn oee-btn">
                                <i class="fas fa-chart-line"></i>
                                Overall Equipment Effectiveness
                            </a>
                        @endif
                        @if(auth()->user()->role === 'leader')
                            @if(auth()->user()->bagian === 'assy')
                                <a href="{{ route('leader.dashboard.assy') }}" class="menu-btn control-btn">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Control Leader
                                </a>
                            @elseif(auth()->user()->bagian === 'inject')
                                <a href="{{ route('leader.dashboard.inject') }}" class="menu-btn control-btn">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Control Leader
                                </a>
                            @endif
                        @elseif(auth()->user()->role === 'foreman')
                                <a href="{{ route('foreman.dashboard.inject') }}" class="menu-btn control-btn">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Control Leader
                                </a>
                        @elseif(auth()->user()->role === 'super_admin')
                            <a href="{{ route('super-admin.dashboard.inject') }}" class="menu-btn control-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                Control Leader
                            </a>
                        @elseif(auth()->user()->role === 'kabag')
                            <a href="{{ route('kabag.dashboard.inject') }}" class="menu-btn control-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                Control Leader
                            </a>
                        @elseif(auth()->user()->role === 'manager_plant')
                            <a href="{{ route('manager-plant.dashboard.inject') }}" class="menu-btn control-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                Control Leader
                            </a>
                        @elseif(auth()->user()->role === 'staff')
                            <a href="{{ route('kabag.dashboard.inject') }}" class="menu-btn control-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                Control Leader
                            </a>
                        @endif
                        @if(in_array(auth()->user()->role, ['super_admin', 'kabag', 'manager_plant', 'staff']))
                            <a href="{{ route('databasekaryawan.index') }}" class="menu-btn employee-btn">
                                <i class="fas fa-users"></i>
                                Database Karyawan
                            </a>
                        @endif
                        @if(in_array(auth()->user()->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'foreman', 'operator']))
                            <a href="{{ route('jadwalkerja.index') }}" class="menu-btn schedule-btn">
                                <i class="fas fa-calendar-alt"></i>
                                Jadwal Kerja
                            </a>
                        @endif
                        @if(!in_array(auth()->user()->role, ['staff']))
                            <a href="{{ route('produksi.index') }}" class="menu-btn production-btn">
                                <i class="fas fa-clipboard-list"></i>
                                Input Hasil Produksi
                            </a>
                            <a href="{{ route('qrcode.index') }}" class="menu-btn qrcode-btn">
                                <i class="fas fa-qrcode"></i>
                                Generate QR Code
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="menu-btn logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-white">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} Digital Production System. All rights reserved.</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>