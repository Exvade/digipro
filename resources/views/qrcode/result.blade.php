<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Generated - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <style>
        .qr-container {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .qr-container:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .qr-code-canvas {
            margin: 10px auto;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 10px;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 150px;
        }

        .print-area {
            background: white;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light no-print">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('qrcode.index') }}" class="nav-link">QR Code Results</a>
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
        <aside class="main-sidebar sidebar-dark-primary elevation-4 no-print">
            <!-- Brand Logo -->
            <a href="{{ route('menu') }}" class="brand-link">
                <img src="{{ asset('images/logo2.png') }}" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">DIGIPRO</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ auth()->user()->nama ?? 'User' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
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
            <section class="content-header no-print">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>QR Code Generated</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('menu') }}">Menu Utama</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('qrcode.index') }}">Generate QR</a></li>
                                <li class="breadcrumb-item active">Results</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    
                    @if(isset($part) && isset($qrCodes))
                    
                    <!-- Success Message -->
                    <div class="alert alert-success no-print">
                        <h4><i class="icon fas fa-check"></i> Success!</h4>
                        Berhasil generate {{ count($qrCodes) }} QR Code untuk part <strong>{{ $part->no_parts }} -
                            {{ $part->nama_part }}</strong>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-3 no-print">
                        <div class="col-12">
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-primary print-all-btn">
                                    <i class="fas fa-print mr-2"></i>
                                    Print Semua
                                </button>
                                <button type="button" class="btn btn-success" id="downloadAll">
                                    <i class="fas fa-download mr-2"></i>
                                    Download Semua
                                </button>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('qrcode.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-plus mr-2"></i>
                                    Generate Lagi
                                </a>
                                <a href="{{ route('menu') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-home mr-2"></i>
                                    Menu Utama
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Part Information -->
                    <div class="card mb-4 no-print">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Part
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID Part:</strong></td>
                                            <td>{{ $part->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. Parts:</strong></td>
                                            <td><span class="badge badge-primary">{{ $part->no_parts }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Part:</strong></td>
                                            <td>{{ $part->nama_part }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>{{ $part->type }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Quantity:</strong></td>
                                            <td>{{ $part->quantity }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bruto:</strong></td>
                                            <td>{{ $part->bruto }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jumlah Generated:</strong></td>
                                            <td><span class="badge badge-success">{{ count($qrCodes) }} QR Code</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu Generate:</strong></td>
                                            <td>{{ date('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Generated By:</strong></td>
                                            <td>{{ auth()->user()->nama ?? 'System' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Codes Grid -->
                    <div class="print-area">
                        <div class="row" id="qrCodesGrid">
                            @foreach ($qrCodes as $index => $qrCode)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="qr-container">
                                        <div class="qr-header mb-2">
                                            <h6 class="mb-1"><strong>{{ $part->no_parts }}</strong></h6>
                                            <small class="text-muted">{{ $part->nama_part }}</small>
                                        </div>

                                        <div class="qr-code-canvas" id="qrCode{{ $index }}"></div>

                                        <div class="qr-footer mt-2">
                                            <small class="text-muted d-block">
                                                ID: {{ $qrCode['unique_id'] }}
                                            </small>
                                            <small class="text-muted">
                                                {{ $qrCode['sequence'] }}/{{ count($qrCodes) }}
                                            </small>
                                        </div>

                                        <div class="qr-actions mt-2 no-print">
                                            <button class="btn btn-sm btn-outline-primary download-btn"
                                                data-index="{{ $index }}"
                                                data-unique-id="{{ $qrCode['unique_id'] }}">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary print-btn"
                                                data-index="{{ $index }}">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    @else
                    
                    <!-- Error Message -->
                    <div class="alert alert-danger">
                        <h4><i class="icon fas fa-exclamation-triangle"></i> Error!</h4>
                        Data part atau QR Code tidak ditemukan. Silakan <a href="{{ route('qrcode.index') }}">generate ulang</a>.
                    </div>
                    
                    @endif
                    
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer no-print">
            <div class="float-right d-none d-sm-inline">
                DIGIPRO v1.0
            </div>
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Digital Production</a>.</strong> All rights reserved.
        </footer>
    </div>

    <form id="downloadForm" action="{{ route('qrcode.download-all') }}" method="POST" style="display: none;">
        @csrf
        <div id="downloadData"></div>
    </form>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- QR Code Libraries (Primary) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
    
    <!-- QR Code JavaScript -->
    <script src="{{ asset('js/qrcode.js') }}"></script>

    <script>
        // Expose QR Codes data to JS
        var qrCodes = @json($qrCodes ?? []);
        var partData = @json($part ?? null);

        console.log('QR Codes data:', qrCodes);
        console.log('Part data:', partData);

        $(document).ready(function() {
            // Generate QR Codes
            if (qrCodes.length > 0) {
                qrCodes.forEach(function(qrCode, index) {
                    try {
                        var qrElement = document.getElementById('qrCode' + index);
                        var qrValue = qrCode.qr_text || qrCode.unique_id || JSON.stringify(qrCode.data);
                        
                        console.log('Generating QR for index ' + index + ':', qrValue);
                        
                        // Try multiple QR generation methods
                        if (typeof QRious !== 'undefined') {
                            // Method 1: QRious
                            var qr = new QRious({
                                element: qrElement,
                                value: qrValue,
                                size: 120,
                                background: 'white',
                                foreground: 'black'
                            });
                        } else if (typeof QRCode !== 'undefined') {
                            // Method 2: QRCode.js
                            var qr = new QRCode(qrElement, {
                                text: qrValue,
                                width: 120,
                                height: 120,
                                colorDark: "#000000",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.M
                            });
                        } else if (typeof qrcode !== 'undefined') {
                            // Method 3: node-qrcode
                            var canvas = document.createElement('canvas');
                            qrElement.appendChild(canvas);
                            
                            qrcode.toCanvas(canvas, qrValue, {
                                width: 120,
                                margin: 2,
                                color: {
                                    dark: '#000000',
                                    light: '#ffffff'
                                }
                            }, function (error) {
                                if (error) {
                                    console.error('QR generation error for index ' + index + ':', error);
                                    qrElement.innerHTML = '<div class="text-warning"><i class="fas fa-qrcode fa-2x"></i><br><small>QR Code</small></div>';
                                }
                            });
                        } else {
                            // Fallback: Show placeholder
                            qrElement.innerHTML = `
                                <div class="text-primary">
                                    <i class="fas fa-qrcode fa-3x"></i><br>
                                    <small>QR Code Generated</small><br>
                                    <small class="text-muted">${qrCode.unique_id}</small>
                                </div>
                            `;
                        }
                        
                    } catch (error) {
                        console.error('Error generating QR code for index ' + index + ':', error);
                        document.getElementById('qrCode' + index).innerHTML = `
                            <div class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                <small>Error generating QR</small>
                            </div>
                        `;
                    }
                });
            } else {
                console.error('No QR codes data found');
            }

            // Print functionality
            $('.print-all-btn').click(function() {
                window.print();
            });

            $('.print-btn').click(function() {
                var index = $(this).data('index');
                var printWindow = window.open('', '', 'height=600,width=800');
                var qrContainer = $(this).closest('.qr-container').clone();
                qrContainer.find('.no-print').remove();
                
                printWindow.document.write('<html><head><title>Print QR Code</title>');
                printWindow.document.write('<style>body{font-family:Arial;text-align:center;padding:20px;}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(qrContainer.html());
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            });

            // Download functionality
            $('.download-btn').click(function() {
                var index = $(this).data('index');
                var canvas = document.querySelector('#qrCode' + index + ' canvas');
                if (canvas) {
                    var link = document.createElement('a');
                    link.download = 'qr_code_' + qrCodes[index].unique_id + '.png';
                    link.href = canvas.toDataURL();
                    link.click();
                }
            });

            $('#downloadAll').click(function() {
                qrCodes.forEach(function(qrCode, index) {
                    var canvas = document.querySelector('#qrCode' + index + ' canvas');
                    if (canvas) {
                        var link = document.createElement('a');
                        link.download = 'qr_code_' + qrCode.unique_id + '.png';
                        link.href = canvas.toDataURL();
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                });
            });
        });
    </script>
</body>
</html>