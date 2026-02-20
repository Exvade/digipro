<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Kerja Saya - Digital Production</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/operator-schedule.css') }}" rel="stylesheet">
    <style>
        .dark-mode-switch {
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 10;
        }

        .theme-switch {
            display: inline-block;
            height: 34px;
            position: relative;
            width: 60px;
        }

        .theme-switch input {
            display: none;
        }

        .slider {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            background-color: #fff;
            bottom: 4px;
            content: "";
            height: 26px;
            left: 4px;
            position: absolute;
            transition: .4s;
            width: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #4f46e5;
            content: "\f185";
            font-size: 14px;
        }

        input:checked+.slider {
            background-color: #1e293b;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
            content: "\f186";
            color: #f1f5f9;
            background-color: #334155;
        }

        @media (max-width: 768px) {
            .dark-mode-switch {
                top: 1rem;
                right: 1rem;
            }

            .theme-switch {
                height: 28px;
                width: 50px;
            }

            .slider:before {
                height: 20px;
                width: 20px;
                bottom: 4px;
                left: 4px;
                font-size: 10px;
            }

            input:checked+.slider:before {
                transform: translateX(22px);
            }
        }
    </style>
</head>

<body>

    <div class="page-header text-center">
        <a href="{{ route('menu') }}" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i> Menu Utama
        </a>
        <div class="dark-mode-switch">
            <label class="theme-switch" for="darkModeToggle">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>
        </div>
        <h1 class="fw-bold mb-2">Jadwal Kerja</h1>
        <p class="opacity-75 mb-0 fs-5" id="greeting-text">Loading...</p>
        <div id="current-datetime" class="mt-2 small opacity-50 fw-medium"></div>
    </div>

    <div class="container mb-5" style="position: relative; z-index: 2;">
        <div class="row g-4 justify-content-center">
            <!-- Sidebar Profile -->
            <div class="col-lg-3">
                <div class="glass-card p-4 text-center animate-up">
                    <div class="profile-avatar">
                        {{ substr($user->nama, 0, 1) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user->nama }}</h4>
                    <div class="mb-4">
                        <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-1 small">
                            {{ strtoupper($user->role) }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="fas fa-briefcase me-1"></i> {{ strtoupper($user->bagian) }}
                        </span>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="stats-card-mini">
                                <span class="stats-val text-primary">{{ $stats['total_hari_kerja'] }}</span>
                                <span class="stats-label">Kerja</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card-mini">
                                <span class="stats-val text-secondary">{{ $stats['hari_libur'] }}</span>
                                <span class="stats-label">Libur</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-4 bg-{{ $statusHariIni['color'] }} bg-opacity-10">
                        <div class="small fw-bold text-{{ $statusHariIni['color'] }} mb-1 opacity-75">STATUS SAAT INI</div>
                        <div class="h6 mb-0 text-{{ $statusHariIni['color'] }} fw-bold">
                            {{ strtoupper($statusHariIni['message']) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="glass-card p-4 animate-up delay-1">
                    <div class="d-flex flex-column gap-4">
                        <!-- Navigation Tabs -->
                        <div class="nav nav-pills nav-pills-custom" id="scheduleTabs" role="tablist">
                            <button class="nav-link active" id="today-tab" data-bs-toggle="pill" data-bs-target="#today-view" type="button">HARI INI</button>
                            <button class="nav-link" id="week-tab" data-bs-toggle="pill" data-bs-target="#week-view" type="button">MINGGU INI</button>
                            <button class="nav-link" id="month-tab" data-bs-toggle="pill" data-bs-target="#month-view" type="button">BULAN INI</button>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="scheduleTabsContent">
                            <!-- Today View -->
                            <div class="tab-pane fade show active" id="today-view">
                                @if($jadwalHariIni)
                                <div class="text-center py-5">
                                    <div class="bg-indigo-soft rounded-pill d-inline-block px-4 py-2 mb-3 text-primary fw-bold">
                                        SHIFT {{ strtoupper($jadwalHariIni->shift) }}
                                    </div>
                                    <h1 class="display-4 fw-bold text-primary mb-4">
                                        @if($jadwalHariIni->shift == 'off')
                                        LIBUR
                                        @else
                                        {{ $jadwalHariIni->formatted_jam_masuk }} <span class="text-muted fw-normal mx-2">-</span> {{ $jadwalHariIni->formatted_jam_keluar }}
                                        @endif
                                    </h1>

                                    @if($jadwalHariIni->shift != 'off')
                                    <div class="row justify-content-center">
                                        <div class="col-md-8">
                                            <div class="p-4 bg-light rounded-4 border-0">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bold"><i class="fas fa-chart-line me-2"></i> Progress Kerja</span>
                                                    <span class="badge bg-primary rounded-pill px-3" id="progress-percentage">0%</span>
                                                </div>
                                                <div class="progress progress-custom">
                                                    <div id="work-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <div class="opacity-25 mb-3"><i class="fas fa-calendar-times display-1"></i></div>
                                    <h5 class="text-muted fw-bold">Tidak ada jadwal dijadwalkan hari ini</h5>
                                </div>
                                @endif
                            </div>

                            <!-- Week View -->
                            <div class="tab-pane fade" id="week-view">
                                <div class="row g-3">
                                    @foreach($jadwalMingguIni as $jadwal)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="schedule-item {{ $jadwal->tanggal->isToday() ? 'today' : '' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">{{ strtoupper($jadwal->tanggal->isoFormat('dddd')) }}</h6>
                                                <span class="badge rounded-pill {{ $jadwal->shift == 'off' ? 'bg-secondary' : 'bg-primary' }} bg-opacity-10 text-{{ $jadwal->shift == 'off' ? 'secondary' : 'primary' }} px-3">
                                                    {{ strtoupper($jadwal->shift) }}
                                                </span>
                                            </div>
                                            <div class="text-muted small fw-bold mb-3 border-bottom pb-2">{{ $jadwal->tanggal->format('d M Y') }}</div>
                                            <div class="small fw-bold">
                                                @if($jadwal->shift == 'off')
                                                <span class="text-secondary"><i class="fas fa-bed me-2"></i>HARI LIBUR</span>
                                                @else
                                                <span class="text-primary"><i class="far fa-clock me-2"></i>{{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Month View -->
                            <div class="tab-pane fade" id="month-view">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-primary btn-sm rounded-pill px-4" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                        <i class="fas fa-filter me-2"></i> Filter Jadwal
                                    </button>
                                </div>

                                <div class="collapse mb-4" id="filterCollapse">
                                    <div class="p-3 bg-light rounded-4 border-0">
                                        <form method="GET" action="{{ route('jadwal-kerja.operator-index') }}" class="row g-2">
                                            <div class="col-md-3">
                                                <select name="bulan" class="form-select border-0 shadow-sm rounded-3">
                                                    @for($i = 1; $i <= 12; $i++)
                                                        <option value="{{ $i }}" {{ (request('bulan') ?: now()->month) == $i ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                                        </option>
                                                        @endfor
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="tahun" class="form-select border-0 shadow-sm rounded-3">
                                                    @for($i = now()->year - 1; $i <= now()->year + 1; $i++)
                                                        <option value="{{ $i }}" {{ (request('tahun') ?: now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-primary w-100 rounded-3">Tampilkan</button>
                                            </div>
                                            <div class="col-md-3">
                                                <a href="{{ route('jadwal-kerja.operator-index') }}" class="btn btn-outline-secondary w-100 rounded-3">Reset</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-custom align-middle">
                                        <thead class="text-muted small">
                                            <tr>
                                                <th class="border-0 px-4">TANGGAL & HARI</th>
                                                <th class="border-0">SHIFT</th>
                                                <th class="border-0">WAKTU KERJA</th>
                                                <th class="border-0">DURASI</th>
                                                <th class="border-0 text-end px-4">STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($jadwalBulanIni as $jadwal)
                                            <tr class="{{ $jadwal->tanggal->isToday() ? 'border border-primary' : '' }}">
                                                <td class="px-4">
                                                    <div class="fw-bold text-primary">{{ strtoupper($jadwal->tanggal->isoFormat('dddd')) }}</div>
                                                    <div class="small fw-bold text-muted">{{ $jadwal->tanggal->format('d / m / Y') }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-{{ $jadwal->shift == 'pagi' ? 'info' : ($jadwal->shift == 'siang' ? 'warning' : ($jadwal->shift == 'malam' ? 'dark' : 'secondary')) }} bg-opacity-10 text-{{ $jadwal->shift == 'pagi' ? 'info' : ($jadwal->shift == 'siang' ? 'warning' : ($jadwal->shift == 'malam' ? 'dark' : 'secondary')) }} px-3 py-2 fw-bold">
                                                        {{ strtoupper($jadwal->shift) }}
                                                    </span>
                                                </td>
                                                <td class="small fw-bold">
                                                    @if($jadwal->shift != 'off')
                                                    {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}
                                                    @else
                                                    <span class="text-muted opacity-50">-</span>
                                                    @endif
                                                </td>
                                                <td class="small fw-bold text-muted">
                                                    {{ $jadwal->shift != 'off' ? $jadwal->durasi_kerja . ' JAM' : '-' }}
                                                </td>
                                                <td class="text-end px-4">
                                                    <span class="badge {{ $jadwal->status == 'aktif' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
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
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
</body>

</html>