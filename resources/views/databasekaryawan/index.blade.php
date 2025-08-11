<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Database Karyawan - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/menu.css') }}" rel="stylesheet">
    <link href="{{ asset('css/database-karyawan.css') }}" rel="stylesheet">
</head>
<body class="menu-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('menu') }}">
                <i class="fas fa-industry me-2"></i>
                Digital Production System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('menu') }}">
                    <i class="fas fa-home me-1"></i>
                    Menu Utama
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="coming-soon-container">
            <div class="coming-soon-card">
                <div class="coming-soon-icon">
                    <i class="fas fa-users"></i>
                </div>
                
                <h1 class="coming-soon-title">Coming Soon!</h1>
                
                <p class="coming-soon-subtitle">Database Karyawan</p>
                
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                
                <p class="coming-soon-description">
                    Fitur database karyawan sedang dalam pengembangan. 
                    Tim kami sedang bekerja keras untuk menghadirkan sistem manajemen 
                    data karyawan yang terintegrasi dan mudah digunakan.
                </p>
                
                <p class="coming-soon-description">
                    <strong>Fitur yang akan hadir:</strong><br>
                    • Data profil karyawan lengkap<br>
                    • Manajemen shift dan jadwal<br>
                    • Riwayat kinerja dan absensi<br>
                    • Laporan data karyawan
                </p>
                
                <a href="{{ route('menu') }}" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali ke Menu Utama
                </a>
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
    <!-- Custom JavaScript -->
    <script src="{{ asset('js/database-karyawan.js') }}"></script>
</body>
</html> 