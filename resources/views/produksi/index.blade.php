<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan QR Code - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/produksi.css') }}" rel="stylesheet">
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
                    <a href="{{ route('produksi.index') }}" class="nav-link">Scan QR Code Product</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
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
                        <a href="{{ route('menu') }}" class="dropdown-item">
                            <i class="fas fa-home mr-2"></i> Menu Utama
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('qrcode.index') }}" class="dropdown-item">
                            <i class="fas fa-qrcode mr-2"></i> Generate QR Code
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('login') }}" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('produksi.index') }}" class="brand-link">
                <img src="{{ asset('images/logo2.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">DIGIPRO</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ auth()->user()->nama ?? 'Operator' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('produksi.index') }}" class="nav-link active">
                                <i class="nav-icon fas fa-camera"></i>
                                <p>Scan QR Code Product</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('qrcode.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-qrcode"></i>
                                <p>Generate QR Code</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('input-produksi.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Input Produksi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('menu') }}" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Menu Utama</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Scan QR Code</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('menu') }}">Menu Utama</a></li>
                                <li class="breadcrumb-item active">Scan QR Code</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-camera mr-2"></i>
                                        Scan QR Code Produk
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <!-- Scan Section -->
                                    <div class="scan-section">
                                        <div class="row justify-content-center">
                                            <div class="col-md-8">
                                                <div class="alert alert-info text-center">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Petunjuk:</strong> Scan QR code yang telah di-generate dari sistem untuk memproses produk
                                                </div>
                                                
                                                <div class="scan-area text-center" id="scanArea">
                                                    <div class="scan-icon mb-3">
                                                        <i class="fas fa-qrcode fa-4x text-primary"></i>
                                                    </div>
                                                    <h4 class="scan-title mb-3">
                                                        <i class="fas fa-camera mr-2"></i>
                                                        Scan QR Code
                                                    </h4>
                                                    <div class="scan-text mb-2">Klik untuk mulai scan QR code</div>
                                                    <div class="scan-subtext text-muted">Pastikan QR code terlihat jelas di kamera</div>
                                                    <button class="btn btn-primary btn-lg mt-3" id="startScanBtn">
                                                        <i class="fas fa-camera mr-2"></i>
                                                        Mulai Scan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Loading State -->
                                    <div class="loading" id="loading">
                                        <div class="spinner"></div>
                                        <p>Memproses scan...</p>
                                    </div>

                                    <!-- Product Information -->
                                    <div class="product-info" id="productInfo">
                                        <!-- Product info will be populated by JavaScript -->
                                    </div>

                                    <!-- Quality Check -->
                                    <div class="quality-check" id="qualityCheck">
                                        <h4 class="quality-title">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Quality Check
                                        </h4>
                                        
                                        <div class="quality-buttons">
                                            <button class="quality-btn good" data-quality="good">
                                                <i class="fas fa-thumbs-up"></i>
                                                GOOD
                                            </button>
                                            <button class="quality-btn ng" data-quality="ng">
                                                <i class="fas fa-times-circle"></i>
                                                NG
                                            </button>
                                            <button class="quality-btn retry" data-quality="retry">
                                                <i class="fas fa-redo"></i>
                                                RETRY
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Scan History -->
                                    <div class="scan-history-section mt-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-history mr-2"></i>
                                                    Riwayat Scan Terakhir
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="scan-history" id="scanHistory">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-history fa-2x mb-2"></i>
                                                        <p>Belum ada riwayat scan</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="container text-center">
                <span class="text-muted">© {{ date('Y') }} All rights reserved | IT Department PT Mada Wikri Tunggal</span>
            </div>
        </footer> 
    </div>

    <!-- Modal for QR Scanner -->
    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode mr-2"></i>
                        Scan QR Code Product
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Penting:</strong> Hanya QR code yang di-generate dari sistem yang dapat di-scan
                        </div>
                        <div id="cameraContainer" class="mb-3">
                            <div id="cameraLoading" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Menginisialisasi kamera...</p>
                            </div>
                            
                            <!-- Camera Status Indicator -->
                            <div id="cameraStatus" class="alert alert-info mb-2" style="display: none;">
                                <i class="fas fa-camera mr-2"></i>
                                <span id="cameraStatusText">Menggunakan kamera belakang</span>
                            </div>
                            
                            <div id="interactive" class="viewport border rounded"></div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Arahkan kamera ke QR code untuk scan otomatis
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Tips: Pastikan QR code berada dalam frame dan pencahayaan cukup
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>
                        Tutup
                    </button>
                    <button type="button" class="btn btn-primary" id="switchCameraBtn" style="display: none;">
                        <i class="fas fa-sync-alt mr-2"></i>
                        <span id="cameraButtonText">Ganti Kamera</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- QR Code Scanner Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <!-- QR Code Library (Local) -->
    <script src="{{ asset('js/qrcode-lib.js') }}"></script>
    <!-- Custom JavaScript -->
    <script src="{{ asset('js/produksi.js') }}"></script>
</body>
</html>