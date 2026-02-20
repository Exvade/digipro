<!-- resources/views/layouts/sidebar.blade.php -->
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <a href="{{ route('menu') }}" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="DIGIPRO Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <div class="sidebar">
        @php $user = auth()->user(); @endphp

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('jadwal-kerja.index') }}"
                        class="nav-link {{ request()->routeIs('jadwal-kerja.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Jadwal Kerja</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>