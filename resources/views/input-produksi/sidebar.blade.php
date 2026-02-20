<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">

    <a href="#" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="DIGIPRO Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                {{-- Kalau user punya avatar, tampilkan. Kalau tidak, pakai default --}}
                <img src="{{ Auth::user()->avatar ?? 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg' }}"
                    class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->nama ?? 'User' }}</a>
            </div>
        </div>

           <!-- Sidebar Menu -->
           <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('produksi.index') }}" class="nav-link ">
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
                    <a href="{{ route('input-produksi.index') }}" class="nav-link active">
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
