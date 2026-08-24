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
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar" style="display: flex; flex-direction: column; height: 100vh;">

    <a href="#" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="DIGIPRO Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar" style="flex: 1; display: flex; flex-direction: column;">
        <!-- Sidebar Menu -->
        <nav class="mt-2" style="flex: 1;">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                @php
                $user = auth()->user();
                $isLeaderAssy = $user->role == 'leader' && $user->bagian == 'assy';
                $isStaffMoldshop = $user->role == 'staff' && $user->departemen == 'Moldshop';
                $isAdminRole = in_array($user->role, ['super_admin', 'kabag', 'manager_plant']);
                @endphp

                <!-- Mold Shop Section -->
                @if ($isStaffMoldshop || (in_array($user->role, ['kabag', 'manager_plant']) && $user->role !== 'super_admin'))
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
                @endif

                @if ($isAdminRole || $isLeaderAssy)
                <li class="nav-header">MASTER DATA</li>

                @if ($isAdminRole)
                <li class="nav-item">
                    <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data User</p>
                    </a>
                </li>
                @endif

                @if ($user->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('moldshop.index') }}" class="nav-link {{ request()->routeIs('moldshop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Data Mold</p>
                    </a>
                </li>
                @endif

                @if ($isAdminRole || ($isLeaderAssy && $user->bagian != 'assy'))
                <li class="nav-item">
                    <a href="{{ route('mesin.index') }}" class="nav-link {{ request()->routeIs('mesin.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-industry"></i>
                        <p>Data Mesin</p>
                    </a>
                </li>
                @endif

                @if ($isAdminRole)
                <li class="nav-item">
                    <a href="{{ route('parts.index') }}" class="nav-link {{ request()->routeIs('parts.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Data Part</p>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a href="{{ route('meja.index') }}" class="nav-link {{ request()->routeIs('meja.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>Data Meja</p>
                    </a>
                </li> -->

                <li class="nav-item">
                    <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>Data Dokumen</p>
                    </a>
                </li>
                @endif
                @endif
            </ul>
        </nav>
    </div>
</aside>