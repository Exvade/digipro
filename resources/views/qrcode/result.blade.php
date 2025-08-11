<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Result - Digital Production System</title>
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
    <!-- QR Code Libraries (Local) -->
    <script src="{{ asset('js/qrcode-lib.js') }}"></script>
    <script>
        // Check if QR code libraries are loaded
        if (typeof QRCode !== 'undefined' && typeof qrcode !== 'undefined') {
            console.log('QR code libraries loaded successfully from local file');
        } else {
            console.log('QR code libraries failed to load from local file');
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
                            <i class="fas fa-camera mr-2"></i> Scan Product
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
                            <h1>QR Code Result</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('menu') }}">Menu Utama</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('qrcode.index') }}">Generate QR Code</a></li>
                                <li class="breadcrumb-item active">Result</li>
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
                                        QR Code Generated Successfully
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <!-- Success Alert -->
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Berhasil!</strong> {{ $quantity }} QR code telah di-generate untuk part <strong>{{ $part->nama_part }}</strong> ({{ $part->no_parts }}).
                                    </div>

                                    <!-- Debug Info -->
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Debug Info:</strong> 
                                        <br>Part ID: {{ $part->id }}
                                        <br>Quantity: {{ $quantity }}
                                        <br>QR Codes Count: {{ count($qrCodes) }}
                                        <br>First QR Data: {{ $qrCodes[0]['qr_text'] ?? 'No data' }}
                                        <br>QRCode Library Status: <span id="qrcodeStatus">Checking...</span>
                                        <br>Browser: <span id="browserInfo"></span>
                                        <br>Timestamp: {{ now()->format('Y-m-d H:i:s') }}
                                        <br>Library Status: <span id="cdnStatus">Loading...</span>
                                        <br>Library Version: <span id="libraryVersion">Unknown</span>
                                    </div>

                                    <!-- Part Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Informasi Part</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong>No. Parts:</strong> {{ $part->no_parts }}</p>
                                                    <p><strong>Nama Part:</strong> {{ $part->nama_part }}</p>
                                                    <p><strong>Type:</strong> {{ $part->type ?? 'N/A' }}</p>
                                                    <p><strong>Quantity Generated:</strong> {{ $quantity }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Cara Penggunaan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <ol>
                                                        <li>Print QR code yang telah di-generate</li>
                                                        <li>Tempel QR code pada part yang sesuai</li>
                                                        <li>Scan QR code menggunakan aplikasi Scan Product</li>
                                                        <li>Data part akan otomatis terdeteksi</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- QR Codes Grid -->
                                    <h4>QR Codes Generated</h4>
                                    <div class="row" id="qrCodesContainer">
                                        @foreach($qrCodes as $index => $qrCode)
                                            <div class="col-md-3 col-sm-6 mb-3">
                                                <div class="qr-code-container">
                                                    <div id="qrCode{{ $index }}" class="text-center"></div>
                                                    <div class="qr-info">
                                                        <small>QR Code #{{ $index + 1 }}</small><br>
                                                        <small>{{ $qrCode['unique_id'] }}</small>
                                                    </div>
                                                    <div class="text-center mt-2">
                                                        <button class="btn btn-sm btn-primary download-btn" 
                                                                data-index="{{ $index }}"
                                                                data-unique-id="{{ $qrCode['unique_id'] }}">
                                                            <i class="fas fa-download mr-1"></i>Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <button class="btn btn-success" id="downloadAll">
                                                <i class="fas fa-download mr-2"></i>
                                                Download All QR Codes
                                            </button>
                                            <a href="{{ route('qrcode.index') }}" class="btn btn-primary ml-2">
                                                <i class="fas fa-plus mr-2"></i>
                                                Generate More QR Codes
                                            </a>
                                            <a href="{{ route('produksi.index') }}" class="btn btn-info ml-2">
                                                <i class="fas fa-camera mr-2"></i>
                                                Test Scan QR Code
                                            </a>
                                            <a href="{{ route('menu') }}" class="btn btn-secondary ml-2">
                                                <i class="fas fa-arrow-left mr-2"></i>
                                                Kembali ke Menu
                                            </a>
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
    <!-- QR Code Data -->
    <script>
        // QR Code data from PHP
        window.qrCodes = @json($qrCodes);
        window.qrCodeDownloadUrl = '{{ route("qrcode.download-all") }}';
    </script>
    <!-- QR Code JavaScript -->
    <script src="{{ asset('js/qrcode.js') }}"></script>
</body>
</html>