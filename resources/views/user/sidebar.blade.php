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
                $canAccessMasterData = in_array($user->role, ['super_admin', 'kabag', 'manager_plant']) ||
                ($user->role == 'staff' && $user->departemen == 'Moldshop') ||
                $isLeaderAssy;
                @endphp

                @if ($canAccessMasterData)
                <li class="nav-header">MASTER DATA</li>

                @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
                <li class="nav-item">
                    <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data User</p>
                    </a>
                </li>
                @endif

                @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']))
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


                @if ($user->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('moldshop.index') }}" class="nav-link {{ request()->routeIs('moldshop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Data Mold</p>
                    </a>
                </li>
                @endif

                @endif
                @endif
            </ul>
        </nav>
    </div>
</aside>