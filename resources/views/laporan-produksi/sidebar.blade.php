<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">

    <a href="#" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="DIGIPRO Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('laporan-produksi.index') }}" class="nav-link ">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Laporan</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
