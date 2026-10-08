<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Kerja Saya - Digital Production System</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Instant Dark Mode Init -->
    <script>
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark', 'dark-mode');
        } else {
            document.documentElement.classList.remove('dark', 'dark-mode');
        }
    </script>

    <!-- Precompiled Tailwind CSS via Vite (Ultra Fast) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased transition-colors duration-300">

    <!-- Hero Header with Indigo Gradient -->
    <div class="relative bg-gradient-to-br from-indigo-700 via-indigo-600 to-indigo-800 text-white pt-6 pb-20 px-4 sm:px-6 lg:px-8 shadow-xl overflow-hidden">
        <!-- Background Ambient Blobs -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Top Action Bar -->
            <div class="flex items-center justify-between gap-4 mb-6">
                <a href="{{ route('menu') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs backdrop-blur-md shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <i class="fas fa-arrow-left text-[11px]"></i>
                    <span>Menu Utama</span>
                </a>

                <!-- Dark Mode Toggle Switch -->
                <div class="flex items-center gap-2">
                    <button type="button" id="darkModeBtn" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white backdrop-blur-md transition-all duration-200 cursor-pointer" title="Toggle Tema">
                        <i class="fas fa-moon dark:hidden text-sm"></i>
                        <i class="fas fa-sun hidden dark:inline text-sm text-amber-300"></i>
                    </button>
                    <!-- Hidden input to maintain compatibility with operator-schedule.js -->
                    <input type="checkbox" id="darkModeToggle" class="hidden">
                </div>
            </div>

            <!-- Greeting Banner -->
            <div class="text-center max-w-2xl mx-auto py-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-[11px] font-bold tracking-wider uppercase backdrop-blur-md mb-3">
                    <i class="fas fa-calendar-check text-indigo-200"></i>
                    <span>Jadwal Kerja Karyawan</span>
                </span>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white mb-2 leading-tight">
                    Jadwal Kerja Saya
                </h1>
                <p class="text-indigo-100 text-xs sm:text-sm font-medium opacity-90 mb-2" id="greeting-text">
                    Selamat datang! Menyiapkan jadwal kerja Anda...
                </p>
                <div id="current-datetime" class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-200 bg-black/15 px-3.5 py-1.5 rounded-full border border-white/10 backdrop-blur-md">
                    <i class="far fa-clock"></i>
                    <span>Memuat waktu...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container (Pulled up into Header) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Sidebar Profile Card (4 Columns) -->
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-5 transition-all">
                    <!-- Avatar & User Info -->
                    <div class="text-center">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-indigo-500/30 mx-auto mb-3.5">
                            {{ substr($user->nama, 0, 1) }}
                        </div>
                        <h2 class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 mb-1 leading-snug">
                            {{ $user->nama }}
                        </h2>
                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/80">
                                {{ strtoupper($user->role) }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-sky-50 dark:bg-sky-950/70 text-sky-600 dark:text-sky-400 border border-sky-200/80 dark:border-sky-800/80">
                                {{ strtoupper($user->bagian ?? 'PRODUKSI') }}
                            </span>
                        </div>
                    </div>

                    <!-- Work & Off Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-center">
                            <span class="block text-2xl font-black text-indigo-600 dark:text-indigo-400 leading-none mb-1">
                                {{ $stats['total_hari_kerja'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Hari Kerja
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-center">
                            <span class="block text-2xl font-black text-slate-500 dark:text-slate-400 leading-none mb-1">
                                {{ $stats['hari_libur'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Hari Libur
                            </span>
                        </div>
                    </div>

                    <!-- Status Saat Ini Card -->
                    @php
                    $statusColorMap = [
                        'success' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                        'primary' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                        'warning' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                        'danger'  => 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                        'info'    => 'bg-sky-50 dark:bg-sky-950/40 text-sky-800 dark:text-sky-300 border-sky-200 dark:border-sky-800/60',
                        'secondary' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                    ];
                    $currentStatusColor = $statusColorMap[$statusHariIni['color']] ?? $statusColorMap['primary'];
                    @endphp
                    <div class="p-4 rounded-2xl border text-center {{ $currentStatusColor }}">
                        <div class="text-[10px] font-bold uppercase tracking-wider opacity-75 mb-1">
                            STATUS HARI INI
                        </div>
                        <div class="text-sm font-black uppercase">
                            {{ strtoupper($statusHariIni['message']) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area: Tabs & Schedules (8 Columns) -->
            <div class="lg:col-span-8 xl:col-span-9">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl space-y-6">

                    <!-- Tab Switcher Navigation -->
                    <div class="flex items-center gap-1.5 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 flex-wrap" id="operatorTabGroup">
                        <button type="button" class="operator-tab-btn active flex-1 py-2 px-3 sm:px-4 rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer" data-target="today-view" id="today-tab">
                            <i class="fas fa-sun mr-1.5 text-amber-500"></i>
                            <span>Hari Ini</span>
                        </button>
                        <button type="button" class="operator-tab-btn flex-1 py-2 px-3 sm:px-4 rounded-xl text-xs font-bold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer" data-target="week-view" id="week-tab">
                            <i class="fas fa-calendar-week mr-1.5 text-sky-500"></i>
                            <span>Minggu Ini</span>
                        </button>
                        <button type="button" class="operator-tab-btn flex-1 py-2 px-3 sm:px-4 rounded-xl text-xs font-bold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer" data-target="month-view" id="month-tab">
                            <i class="fas fa-calendar-alt mr-1.5 text-indigo-500"></i>
                            <span>Bulan Ini</span>
                        </button>
                    </div>

                    <!-- ==================== TAB 1: TODAY VIEW ==================== -->
                    <div class="operator-tab-panel" id="today-view">
                        @if($jadwalHariIni)
                        <div class="text-center py-6 sm:py-8 space-y-6">
                            <!-- Shift Pill Badge -->
                            <div>
                                @php
                                $todayShiftColors = [
                                    'pagi' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-300/40',
                                    'siang' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-300/40',
                                    'malam' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 border-indigo-300/40',
                                    'off' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border-slate-300/40',
                                ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-wider {{ $todayShiftColors[$jadwalHariIni->shift] ?? $todayShiftColors['off'] }}">
                                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                    <span>SHIFT {{ strtoupper($jadwalHariIni->shift) }}</span>
                                </span>
                            </div>

                            <!-- Big Time Display -->
                            <div>
                                @if($jadwalHariIni->shift == 'off')
                                <div class="text-4xl sm:text-6xl font-black text-slate-400 dark:text-slate-500 tracking-tight">
                                    HARI LIBUR
                                </div>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 font-medium">
                                    Tidak ada jadwal shift kerja pada hari ini. Selamat beristirahat!
                                </p>
                                @else
                                <div class="text-4xl sm:text-6xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                                    {{ $jadwalHariIni->formatted_jam_masuk }}
                                    <span class="text-slate-300 dark:text-slate-600 font-light mx-2">-</span>
                                    {{ $jadwalHariIni->formatted_jam_keluar }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-semibold">
                                    Durasi Kerja: {{ $jadwalHariIni->durasi_kerja }} Jam • Bagian {{ ucfirst($user->bagian ?? 'Produksi') }}
                                </div>
                                @endif
                            </div>

                            <!-- Real-time Progress Bar (if working) -->
                            @if($jadwalHariIni->shift != 'off')
                            <div class="max-w-md mx-auto p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                        <i class="fas fa-chart-line text-indigo-500"></i> Progres Waktu Shift
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-[11px] font-black" id="progress-percentage">
                                        0%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                    <div id="work-progress" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @else
                        <!-- No Schedule Empty State -->
                        <div class="text-center py-12 space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto text-2xl">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak Ada Jadwal Hari Ini</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm mx-auto">
                                Belum ada jadwal penugasan yang tercatat untuk tanggal hari ini. Silakan hubungi Leader / Foreman Anda.
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- ==================== TAB 2: WEEK VIEW ==================== -->
                    <div class="operator-tab-panel hidden" id="week-view">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5">
                            @foreach($jadwalMingguIni as $jadwal)
                            @php
                            $isToday = $jadwal->tanggal->isToday();
                            @endphp
                            <div class="p-4 rounded-2xl border transition-all duration-200 {{ $isToday ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-300 dark:border-indigo-700 shadow-md ring-2 ring-indigo-500/20' : 'bg-slate-50/60 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-700/60' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-black uppercase {{ $isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-800 dark:text-slate-200' }}">
                                            {{ strtoupper($jadwal->tanggal->isoFormat('dddd')) }}
                                        </span>
                                        @if($isToday)
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-indigo-600 text-white">HARI INI</span>
                                        @endif
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $jadwal->shift == 'off' ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-300/40' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' }}">
                                        {{ strtoupper($jadwal->shift) }}
                                    </span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 pb-2 border-b border-slate-200/70 dark:border-slate-700/60 mb-2.5">
                                    {{ $jadwal->tanggal->format('d M Y') }}
                                </div>
                                <div class="text-xs font-bold">
                                    @if($jadwal->shift == 'off')
                                    <span class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                        <i class="fas fa-bed text-slate-400"></i> HARI LIBUR
                                    </span>
                                    @else
                                    <span class="text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 font-semibold">
                                        <i class="far fa-clock"></i> {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ==================== TAB 3: MONTH VIEW ==================== -->
                    <div class="operator-tab-panel hidden space-y-4" id="month-view">
                        <!-- Filter Toggle & Collapse -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Rekap Jadwal Bulanan
                            </span>
                            <button type="button" id="toggleFilterBtn" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                <i class="fas fa-filter text-[10px]"></i>
                                <span>Filter Bulan/Tahun</span>
                            </button>
                        </div>

                        <!-- Filter Form Box -->
                        <div id="filterCollapseBox" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 hidden">
                            <form method="GET" action="{{ route('jadwal-kerja.operator-index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                                    <select name="bulan" class="w-full px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                                        @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ (request('bulan') ?: now()->month) == $i ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                        </option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                                    <select name="tahun" class="w-full px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                                        @for($i = now()->year - 1; $i <= now()->year + 1; $i++)
                                        <option value="{{ $i }}" {{ (request('tahun') ?: now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="w-full px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-sm transition-all cursor-pointer">
                                        Tampilkan
                                    </button>
                                </div>
                                <div class="flex items-end">
                                    <a href="{{ route('jadwal-kerja.operator-index') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-center font-bold text-xs shadow-sm transition-all cursor-pointer">
                                        Reset
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Monthly Table -->
                        <div class="overflow-x-auto rounded-2xl border border-slate-200/90 dark:border-slate-800">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        <th class="py-3 px-4">Tanggal & Hari</th>
                                        <th class="py-3 px-3 text-center">Shift</th>
                                        <th class="py-3 px-4">Waktu Kerja</th>
                                        <th class="py-3 px-3 text-center">Durasi</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    @foreach($jadwalBulanIni as $jadwal)
                                    @php
                                    $isToday = $jadwal->tanggal->isToday();
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors {{ $isToday ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="font-bold {{ $isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-800 dark:text-slate-100' }}">
                                                {{ strtoupper($jadwal->tanggal->isoFormat('dddd')) }}
                                                @if($isToday)
                                                <span class="ml-1.5 px-1.5 py-0.2 rounded-full text-[9px] font-black bg-indigo-600 text-white">Hari Ini</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                                {{ $jadwal->tanggal->format('d / m / Y') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @php
                                            $shiftMonthBadges = [
                                                'pagi' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300/40',
                                                'siang' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300/40',
                                                'malam' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border-indigo-300/40',
                                                'off' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border-slate-300/40',
                                            ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $shiftMonthBadges[$jadwal->shift] ?? $shiftMonthBadges['off'] }}">
                                                {{ strtoupper($jadwal->shift) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                            @if($jadwal->shift != 'off')
                                            {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}
                                            @else
                                            <span class="text-slate-400 dark:text-slate-500 italic">Libur</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center text-slate-500 dark:text-slate-400 font-bold text-[11px] whitespace-nowrap">
                                            {{ $jadwal->shift != 'off' ? $jadwal->durasi_kerja . ' Jam' : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $jadwal->status == 'aktif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300/40' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300/40' }}">
                                                {{ strtoupper($jadwal->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Scripts Data Injection for operator-schedule.js -->
    @if($jadwalHariIni && $jadwalHariIni->shift !== 'off')
    <script>
        window.scheduleData = {
            hasSchedule: true,
            jamMasuk: '{{ $jadwalHariIni->jam_masuk }}',
            jamKeluar: '{{ $jadwalHariIni->jam_keluar }}'
        };
    </script>
    @else
    <script>
        window.scheduleData = {
            hasSchedule: false
        };
    </script>
    @endif

    <script>
        window.userData = {
            nama: "{{ $user->nama }}"
        };
    </script>
    <script src="{{ asset('js/operator-schedule.js') }}"></script>

    <!-- Interactive Tabs & Collapse Controller -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode button handler
            const darkModeBtn = document.getElementById('darkModeBtn');
            const darkModeToggle = document.getElementById('darkModeToggle');
            if (darkModeBtn && darkModeToggle) {
                darkModeBtn.addEventListener('click', function() {
                    const isDark = document.documentElement.classList.toggle('dark');
                    document.documentElement.classList.toggle('dark-mode', isDark);
                    document.body.classList.toggle('dark-mode', isDark);
                    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
                    darkModeToggle.checked = isDark;
                });
            }

            // Tab Switcher
            const tabButtons = document.querySelectorAll('.operator-tab-btn');
            const tabPanels = document.querySelectorAll('.operator-tab-panel');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');

                    tabButtons.forEach(b => {
                        b.classList.remove('active', 'bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
                        b.classList.add('text-slate-500', 'dark:text-slate-400');
                    });

                    this.classList.add('active', 'bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
                    this.classList.remove('text-slate-500', 'dark:text-slate-400');

                    tabPanels.forEach(panel => {
                        if (panel.id === targetId) {
                            panel.classList.remove('hidden');
                        } else {
                            panel.classList.add('hidden');
                        }
                    });
                });
            });

            // Initialize active tab style
            const activeTab = document.querySelector('.operator-tab-btn.active');
            if (activeTab) {
                activeTab.classList.add('bg-white', 'dark:bg-slate-700', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
                activeTab.classList.remove('text-slate-500', 'dark:text-slate-400');
            }

            // Filter Toggle in Month View
            const toggleFilterBtn = document.getElementById('toggleFilterBtn');
            const filterCollapseBox = document.getElementById('filterCollapseBox');
            if (toggleFilterBtn && filterCollapseBox) {
                toggleFilterBtn.addEventListener('click', function() {
                    filterCollapseBox.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>

</html>