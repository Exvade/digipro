@php
$user = Auth::user();
$dashboardRoute = route('menu'); // Default fallback to menu
if ($user) {
if ($user->role === 'leader') {
$dashboardRoute = route('leader.dashboard.inject');
} elseif ($user->role === 'super_admin') {
$dashboardRoute = route('super-admin.dashboard.inject');
} elseif ($user->role === 'kabag') {
$dashboardRoute = route('kabag.dashboard.inject');
} elseif ($user->role === 'manager_plant') {
$dashboardRoute = route('manager-plant.dashboard.inject');
} elseif ($user->role === 'staff') {
$dashboardRoute = route('staff.dashboard.inject');
}
}
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <a href="{{ $dashboardRoute }}" class="brand-link">
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

                <li class="nav-header">MOLD SHOP</li>

                <li class="nav-item">
                    <a href="{{ route('moldshop.index') }}" class="nav-link {{ request()->routeIs('moldshop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Data Mold</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('moldshop.history') }}" class="nav-link {{ request()->routeIs('moldshop.history') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Riwayat Mold</p>
                    </a>
                </li>

                @if ($user && in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                <li class="nav-header">MASTER DATA</li>

                <li class="nav-item">
                    <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data User</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('parts.index') }}" class="nav-link {{ request()->routeIs('parts.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Data Part</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('meja.index') }}" class="nav-link {{ request()->routeIs('meja.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>Data Meja</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>Data Dokumen</p>
                    </a>
                </li>
                @endif

                <li class="nav-item mt-2">
                    <a href="{{ route('menu') }}" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Menu Utama</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>