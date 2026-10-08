@php
$hideSidebar = !Auth::check() || request()->routeIs('*.dashboard.inject') || request()->routeIs('*.dashboard.assy') || request()->routeIs('dashboard.inject.public');
@endphp
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 transition-colors duration-300">
    <div class="flex items-center gap-3">
        @if(!$hideSidebar)
        <button id="sidebarToggleBtn" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle Sidebar">
            <i class="fas fa-bars text-lg"></i>
        </button>
        @endif

        @if($hideSidebar)
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo bulat.png') }}" alt="Logo" class="w-8 h-8 object-contain">
            <div>
                <h1 class="text-sm font-extrabold text-slate-900 dark:text-white leading-tight">
                    @if(Auth::check() && Auth::user()->role == 'leader')
                    Andon System PT. Mada Wikri Tunggal
                    @else
                    Dashboard Monitoring Produksi
                    @endif
                </h1>
                <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">Plant 3 - Plastic Injection</p>
            </div>
        </div>
        @else
        <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('menu') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">Home</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">@yield('title', 'System')</span>
        </div>
        @endif
    </div>

    @if($hideSidebar)
    <div class="hidden md:flex items-center gap-4 px-4 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
            <i class="fas fa-calendar-alt text-indigo-500"></i>
            <span id="realtimeDate">-</span>
        </div>
        <div class="h-3 w-px bg-slate-300 dark:bg-slate-600"></div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-100">
            <i class="fas fa-clock text-indigo-500"></i>
            <span id="realtimeTime">-</span>
        </div>
    </div>
    @endif

    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Dark Mode Toggle Switch -->
        <button id="headerDarkModeToggle" class="p-2.5 rounded-full text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle Dark Mode">
            <i class="fas fa-moon text-base dark:hidden"></i>
            <i class="fas fa-sun text-base hidden dark:block text-amber-400"></i>
        </button>

        <!-- Notification Bell -->
        <div class="relative">
            <button id="notifDropdownBtn" class="relative p-2.5 rounded-full text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Notifications">
                <i class="fas fa-bell text-base"></i>
                <span class="navbar-badge absolute top-1 right-1 hidden w-2 h-2 bg-amber-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
            </button>
            <div id="notifDropdownMenu" class="hidden absolute right-0 mt-2 w-80 bg-white dark:!bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-3 z-50">
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 px-2 py-1 uppercase tracking-wider">Notifikasi</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-60 overflow-y-auto my-1">
                    <div class="px-2 py-3 text-center text-xs text-slate-400">Tidak ada notifikasi</div>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        @auth
        <div class="relative">
            <button id="userDropdownBtn" class="flex items-center gap-2 p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=4f46e5&color=fff&bold=true" class="w-8 h-8 rounded-full border border-indigo-200 dark:border-indigo-800 object-cover" alt="User">
                <span class="hidden md:inline text-xs font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->nama }}</span>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden md:inline"></i>
            </button>
            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:!bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50">
                <a href="{{ route('menu') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home text-indigo-500"></i> Menu Utama
                </a>
                <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                <button onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition-colors text-left">
                    <i class="fas fa-sign-out-alt text-rose-500"></i> Logout
                </button>
                <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
        @else
        <a href="{{ route('login') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-full shadow-sm transition-colors">
            <i class="fas fa-sign-in-alt mr-1"></i> Login
        </a>
        @endauth
    </div>
</header>