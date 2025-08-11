<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Generate QR Code - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- QR Code CSS -->
    <link href="{{ asset('css/qrcode.css') }}" rel="stylesheet">
    <!-- QR Code Libraries (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // Check if QR code libraries are loaded
        if (typeof QRCode !== 'undefined' && typeof qrcode !== 'undefined') {
            console.log('QR code libraries loaded successfully from CDN');
        } else {
            console.log('QR code libraries failed to load from CDN');
            // Add fallback if needed
        }
    </script>
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
                    <a href="{{ route('qrcode.index') }}" class="nav-link">Generate QR Code</a>
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
                        <a href="{{ route('produksi.index') }}" class="dropdown-item">
                            <i class="fas fa-qrcode mr-2"></i> Scan Product
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
            <a href="{{ route('menu') }}" class="brand-link">
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
                        <a href="#" class="d-block">{{ auth()->user()->nama ?? 'User' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('qrcode.index') }}" class="nav-link active">
                                <i class="nav-icon fas fa-qrcode"></i>
                                <p>Generate QR Code</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('produksi.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-camera"></i>
                                <p>Scan Product</p>
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
                            <h1>Generate QR Code</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('menu') }}">Menu Utama</a></li>
                                <li class="breadcrumb-item active">Generate QR Code</li>
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
                                        <i class="fas fa-qrcode mr-2"></i>
                                        Generate QR Code untuk Part
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('qrcode.generate') }}" method="POST" id="qrForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="part_id">Pilih Part <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="part_id" name="part_id" required>
                                                        <option value="">Pilih Part...</option>
                                                        @foreach($parts as $part)
                                                            <option value="{{ $part->id }}" 
                                                                    data-no-parts="{{ $part->no_parts }}"
                                                                    data-nama-part="{{ $part->nama_part }}">
                                                                {{ $part->no_parts }} - {{ $part->nama_part }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="quantity">Jumlah QR Code <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                                           min="1" max="100" value="1" required>
                                                    <small class="form-text text-muted">Maksimal 100 QR code per generate</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Informasi:</strong> QR code akan berisi data part yang dipilih dan dapat di-scan menggunakan aplikasi scan product.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-qrcode mr-2"></i>
                                                    Generate QR Code
                                                </button>
                                                <a href="{{ route('menu') }}" class="btn btn-secondary ml-2">
                                                    <i class="fas fa-arrow-left mr-2"></i>
                                                    Kembali
                                                </a>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Preview Section -->
                                    <div class="mt-4" id="previewSection" style="display: none;">
                                        <h4>Preview QR Code</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <div id="qrPreview"></div>
                                                        <p class="mt-2 text-muted" id="qrText"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">Informasi Part</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="partInfo">
                                                            <p class="text-muted">Pilih part untuk melihat informasi</p>
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
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                DIGIPRO v1.0
            </div>
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Digital Production</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- QR Code JavaScript -->
    <script src="{{ asset('js/qrcode.js') }}"></script>
</body>
</html>