<dl class="row mb-0">
    <dt class="col-sm-3">Nama Karyawan</dt>
    <dd class="col-sm-9">{{ $jadwal->user->nama ?? '-' }}</dd>

    <dt class="col-sm-3">Departemen</dt>
    <dd class="col-sm-9">{{ $jadwal->user->departemen ?? '-' }}</dd>

    <dt class="col-sm-3">Role</dt>
    <dd class="col-sm-9">{{ ucfirst($jadwal->user->role ?? '-') }}</dd>

    <dt class="col-sm-3">Tanggal</dt>
    <dd class="col-sm-9">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}</dd>

    <dt class="col-sm-3">Hari</dt>
    <dd class="col-sm-9">{{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd') }}</dd>

    <dt class="col-sm-3">Shift</dt>
    <dd class="col-sm-9">
        <span
            class="badge badge-{{ $jadwal->shift == 'pagi' ? 'success' : ($jadwal->shift == 'siang' ? 'warning' : ($jadwal->shift == 'malam' ? 'info' : 'secondary')) }}">
            {{ ucfirst($jadwal->shift) }}
        </span>
    </dd>

    <dt class="col-sm-3">Jam Kerja</dt>
    <dd class="col-sm-9">
        @if ($jadwal->shift !== 'off')
            {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}
            ({{ $jadwal->durasi_kerja }} jam)
        @else
            <span class="text-muted">Libur</span>
        @endif
    </dd>

    <dt class="col-sm-3">Bagian</dt>
    <dd class="col-sm-9">{{ ucfirst($jadwal->user->bagian ?? '-') }}</dd>

    <dt class="col-sm-3">Grup</dt>
    <dd class="col-sm-9">
        {{ $jadwal->user && $jadwal->user->grup ? strtoupper($jadwal->user->grup->nama_grup) : '-' }}
    </dd>

    <dt class="col-sm-3">Status</dt>
    <dd class="col-sm-9">
        <span class="badge {{ ($jadwal->status ?? '') === 'aktif' ? 'badge-success' : 'badge-danger' }}">
            {{ ucfirst($jadwal->status ?? '-') }}
        </span>
    </dd>
</dl>
