<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Menu Utama - Digital Production System</title>

    <!-- Instant Dark Mode Initialization to prevent flash -->
    <script>
        (function() {
            var isDark = localStorage.getItem('darkMode') === 'enabled';
            if (isDark) {
                document.documentElement.classList.add('dark', 'dark-mode');
            } else {
                document.documentElement.classList.remove('dark', 'dark-mode');
            }
        })();
    </script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Precompiled Tailwind CSS via Vite (Ultra Fast) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-300 flex flex-col justify-between">
    <!-- Animated Ambient Background -->
    <div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50/90 via-slate-100 to-sky-50/80 dark:from-slate-950 dark:via-indigo-950/30 dark:to-slate-950 pointer-events-none transition-colors duration-300"></div>
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_15%_15%,rgba(99,102,241,0.12),transparent_40%),radial-gradient(circle_at_85%_85%,rgba(14,165,233,0.1),transparent_40%)] pointer-events-none"></div>

    <!-- Top Floating Header Bar -->
    <header class="fixed top-4 left-4 right-4 z-50 flex items-center justify-between pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-2.5 px-4 py-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-full shadow-sm">
            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-xs shadow-sm">
                <i class="fas fa-industry"></i>
            </div>
            <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-1.5">
                DIGIPRO <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-1.5 py-0.5 rounded-md">SYSTEM</span>
            </span>
        </div>

        <!-- Modern Clean Theme Toggle Button (Light/Dark Switcher) -->
        <div class="pointer-events-auto">
            <button id="menuThemeToggleBtn" type="button" class="group relative flex items-center gap-2 px-3 py-1.5 bg-white/95 dark:bg-slate-900/95 hover:bg-white dark:hover:bg-slate-800 backdrop-blur-md border border-slate-200/90 dark:border-slate-700/80 rounded-full shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer" title="Ganti Mode Terang / Gelap" aria-label="Toggle Theme">
                <span class="w-6 h-6 rounded-full flex items-center justify-center bg-amber-100 text-amber-600 dark:bg-indigo-950/80 dark:text-amber-400 shadow-sm transition-transform duration-200 group-hover:rotate-12">
                    <i class="fas fa-sun text-xs dark:hidden"></i>
                    <i class="fas fa-moon text-xs hidden dark:inline text-indigo-300"></i>
                </span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 pr-1 select-none">
                    <span class="dark:hidden">Light</span>
                    <span class="hidden dark:inline">Dark</span>
                </span>
            </button>
        </div>
    </header>

    <div class="min-h-screen flex flex-col justify-between pt-20 pb-10 px-4 sm:px-6">
        <main class="w-full max-w-4xl mx-auto my-auto py-6">
            <div class="bg-white/95 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800/80 rounded-3xl p-6 sm:p-10 shadow-xl shadow-slate-200/60 dark:shadow-none transition-all duration-300">
                
                <!-- User Profile & Greeting Section -->
                <div class="text-center mb-8">
                    <div class="relative inline-block mb-3">
                        <div class="p-1 bg-gradient-to-tr from-indigo-600 via-sky-500 to-indigo-400 rounded-full shadow-lg shadow-indigo-500/25">
                            <img class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-4 border-white dark:border-slate-900 object-cover"
                                src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama ?? 'User') }}&background=4f46e5&color=fff&size=120&font-size=0.45"
                                alt="Avatar"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 120 120\'><rect width=\'120\' height=\'120\' fill=\'%234f46e5\'/><text x=\'50%25\' y=\'55%25\' font-family=\'sans-serif\' font-size=\'48\' font-weight=\'bold\' fill=\'%23ffffff\' text-anchor=\'middle\' dominant-baseline=\'middle\'>{{ strtoupper(substr(Auth::user()->nama ?? 'U', 0, 1)) }}</text></svg>';">
                        </div>
                        <span class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full shadow-md" title="Status Aktif"></span>
                    </div>

                    @php
                        $hour = (int) date('H');
                        if ($hour >= 5 && $hour < 11) {
                            $greeting = 'Selamat Pagi';
                            $greetingIcon = 'fa-sun text-amber-500';
                        } elseif ($hour >= 11 && $hour < 15) {
                            $greeting = 'Selamat Siang';
                            $greetingIcon = 'fa-cloud-sun text-amber-500';
                        } elseif ($hour >= 15 && $hour < 18) {
                            $greeting = 'Selamat Sore';
                            $greetingIcon = 'fa-cloud-sun text-orange-500';
                        } else {
                            $greeting = 'Selamat Malam';
                            $greetingIcon = 'fa-moon text-indigo-400';
                        }
                    @endphp

                    <div>
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100/80 dark:border-indigo-800/40 text-indigo-700 dark:text-indigo-300 font-bold text-xs rounded-full uppercase tracking-wider mb-2.5 shadow-sm">
                            <i class="fas {{ $greetingIcon }}"></i> {{ $greeting }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-3">
                        {{ ucfirst(Auth::user()->nama) }}
                    </h1>

                    <div class="flex items-center justify-center flex-wrap gap-2">
                        <span class="px-3.5 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-full flex items-center gap-1.5">
                            <i class="fas fa-user-shield"></i> {{ ucwords(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}
                        </span>
                        @if(!empty(Auth::user()->departemen))
                            <span class="px-3.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-full flex items-center gap-1.5">
                                <i class="fas fa-building"></i> {{ Auth::user()->departemen }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Section Divider -->
                <div class="relative flex py-4 items-center mb-6">
                    <div class="flex-grow border-t border-dashed border-slate-300 dark:border-slate-800"></div>
                    <span class="flex-shrink mx-4 text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase">Navigasi Utama</span>
                    <div class="flex-grow border-t border-dashed border-slate-300 dark:border-slate-800"></div>
                </div>

                <!-- Grid Menu Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    @php
                        $user = auth()->user();
                        $isPPICRole = in_array($user->role, ['kabag', 'staff']) &&
                            in_array($user->departemen, [
                                'PPIC',
                                'Production Planning Inventory Control',
                                'Production Planning & Inventory Control',
                            ]);

                        $isKabagPPIC = $user->role == 'kabag' && $isPPICRole;
                    @endphp

                    @if (!$isPPICRole)
                        {{-- Master Data --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant']) ||
                                ($user->role == 'staff' && $user->departemen == 'Moldshop') ||
                                ($user->role == 'leader' && $user->bagian == 'assy'))
                            @php
                                $masterRoute = route('user.index');
                                if ($user->role == 'staff' && $user->departemen == 'Moldshop') {
                                    $masterRoute = route('moldshop.index');
                                } elseif ($user->role == 'leader' && $user->bagian == 'assy') {
                                    $masterRoute = route('meja.index');
                                }
                            @endphp
                            <a href="{{ $masterRoute }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-sky-500/50 dark:hover:border-sky-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-sky-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-sky-100 dark:bg-sky-950/70 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Master Data</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manajemen Data & Master User</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-sky-600 dark:group-hover:text-sky-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif

                        {{-- Data Mesin --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader']) &&
                                !($user->role == 'leader' && $user->bagian == 'assy'))
                            <a href="{{ route('mesin.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-blue-500/50 dark:hover:border-blue-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-blue-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Data Mesin</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Informasi & Status Mesin Produksi</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif

                        {{-- Jadwal Pemeliharaan Mesin --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'operator']) &&
                                !(
                                    ($user->role == 'leader' || $user->role == 'operator') &&
                                    in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant'])
                                ))
                            <a href="{{ route('jadwal-pemeliharaan.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-orange-500/50 dark:hover:border-orange-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-orange-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-orange-100 dark:bg-orange-950/70 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-wrench"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Jadwal Pemeliharaan</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Pemeliharaan & Service Mesin</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-orange-600 dark:group-hover:text-orange-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif

                        {{-- Jadwal Kerja --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff', 'leader', 'foreman', 'operator']) &&
                                (in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant', 'Repair & Maintenance']) ||
                                    in_array($user->bagian, ['inject', 'assy'])))
                            <a href="{{ route('jadwal-kerja.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-pink-500/50 dark:hover:border-pink-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-pink-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-pink-100 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">Jadwal Kerja</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manpower & Shift Work Schedule</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-pink-600 dark:group-hover:text-pink-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif
                    @endif

                    {{-- Planning Produksi --}}
                    @if ($user->role === 'super_admin' || (in_array($user->role, ['kabag', 'staff', 'manager_plant']) &&
                            in_array($user->departemen, ['Production Planning Inventory Control', 'Information Technology'])))
                        <a href="{{ route('planning.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-emerald-500/50 dark:hover:border-emerald-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-emerald-500 transition-colors duration-200"></div>
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Planning Produksi</span>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Rencana & Target Produksi</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:translate-x-1 transition-all duration-200"></i>
                        </a>
                    @endif

                    {{-- Dashboard Section --}}
                    @php
                        $showAssy = false;
                        $showInject = false;
                        $assyRoute = '#';
                        $injectRoute = '#';

                        if (
                            in_array($user->role, [
                                'super_admin',
                                'kabag',
                                'manager_plant',
                                'staff',
                                'foreman',
                            ]) &&
                            in_array($user->departemen, [
                                'Production Planning Inventory Control',
                                'Information Technology',
                                'Manager Plant',
                                'Produksi',
                            ]) &&
                            !$isPPICRole
                        ) {
                            $showAssy = true;
                            $showInject = true;

                            if ($user->role === 'super_admin') {
                                $assyRoute = route('super-admin.dashboard.assy');
                                $injectRoute = route('super-admin.dashboard.inject');
                            } elseif ($user->role === 'kabag') {
                                $assyRoute = route('kabag.dashboard.assy');
                                $injectRoute = route('kabag.dashboard.inject');
                            } elseif ($user->role === 'manager_plant') {
                                $assyRoute = route('manager-plant.dashboard.assy');
                                $injectRoute = route('manager-plant.dashboard.inject');
                            } elseif ($user->role === 'staff') {
                                $assyRoute = route('staff.dashboard.assy');
                                $injectRoute = route('staff.dashboard.inject');
                            } elseif ($user->role === 'foreman') {
                                $assyRoute = route('foreman.dashboard.assy');
                                $injectRoute = route('foreman.dashboard.inject');
                            } else {
                                $assyRoute = route('leader.dashboard.assy');
                                $injectRoute = route('leader.dashboard.inject');
                            }
                        } elseif ($user->role === 'leader') {
                            if ($user->bagian === 'assy') {
                                $showAssy = true;
                                $assyRoute = route('leader.dashboard.assy');
                            } elseif ($user->bagian === 'inject') {
                                $showInject = true;
                                $injectRoute = route('leader.dashboard.inject');
                            }
                        }
                    @endphp

                    @if ($showInject)
                        <a href="{{ $injectRoute }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-indigo-500/50 dark:hover:border-indigo-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-indigo-500 transition-colors duration-200"></div>
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                    <i class="fas fa-cubes"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Dashboard Inject</span>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Monitoring Injeksi Real-time</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all duration-200"></i>
                        </a>
                    @endif

                    @if ($showAssy)
                        <a href="{{ $assyRoute }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-cyan-500 transition-colors duration-200"></div>
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-cyan-100 dark:bg-cyan-950/70 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">Dashboard Assy</span>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Monitoring Assembly Real-time</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 group-hover:translate-x-1 transition-all duration-200"></i>
                        </a>
                    @endif

                    @if (!$isPPICRole)
                        {{-- Data Dokumen --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'leader', 'foreman', 'staff']))
                            <a href="{{ route('documents.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-teal-500/50 dark:hover:border-teal-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-teal-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-teal-100 dark:bg-teal-950/70 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">Data Dokumen</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Arsip & Dokumentasi Sistem</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-teal-600 dark:group-hover:text-teal-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif

                        {{-- Control Leader --}}
                        @if (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'foreman', 'leader']))
                            <a href="{{ route('ploting.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-purple-500/50 dark:hover:border-purple-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                                <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-purple-500 transition-colors duration-200"></div>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Control Leader</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Ploting & Kontrol Area Kerja</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-purple-600 dark:group-hover:text-purple-400 group-hover:translate-x-1 transition-all duration-200"></i>
                            </a>
                        @endif
                    @endif

                    {{-- Input Hasil Produksi (Khusus role operator yang tidak memiliki akses Control Leader) --}}
                    @if ($user->role === 'operator' && !$isPPICRole)
                        <a href="{{ route('input-produksi.index') }}" class="group relative flex items-center justify-between p-4 bg-white dark:bg-slate-800/50 hover:bg-slate-50/90 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/60 hover:border-amber-500/50 dark:hover:border-amber-500/50 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-transparent group-hover:bg-amber-500 transition-colors duration-200"></div>
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Input Hasil Produksi</span>
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Catatan & Output Produksi</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-amber-600 dark:group-hover:text-amber-400 group-hover:translate-x-1 transition-all duration-200"></i>
                        </a>
                    @endif
                </div>

                <!-- Footer Action (Logout) -->
                <form action="{{ route('logout') }}" method="POST" class="w-full mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 p-3.5 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 rounded-2xl font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-rose-500/10 cursor-pointer">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar dari Sistem (Logout)</span>
                    </button>
                </form>
            </div>
        </main>

        <footer class="py-6 text-center text-xs text-slate-500 dark:text-slate-400">
            © {{ date('Y') }} <strong class="text-slate-700 dark:text-slate-300">DIGIPRO</strong> • IT Department PT Mada Wikri Tunggal
        </footer>
    </div>

    <!-- Theme Controller Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('menuThemeToggleBtn');

            function applyTheme(isDark) {
                const html = document.documentElement;
                const body = document.body;

                if (isDark) {
                    html.classList.add('dark', 'dark-mode');
                    if (body) body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    html.classList.remove('dark', 'dark-mode');
                    if (body) body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isCurrentlyDark = document.documentElement.classList.contains('dark');
                    applyTheme(!isCurrentlyDark);
                });
            }

            // Keyboard shortcut: Ctrl+D or Cmd+D
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
                    e.preventDefault();
                    const isCurrentlyDark = document.documentElement.classList.contains('dark');
                    applyTheme(!isCurrentlyDark);
                }
            });
        });
    </script>
</body>

</html>
