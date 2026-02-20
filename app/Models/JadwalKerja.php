<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class JadwalKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'shift',
        'jam_masuk',
        'jam_keluar',
        'bagian',
        'keterangan',
        'status',
        'created_by'
    ];


    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
    ];

    // ======================= Relasi =======================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ======================= Scope =======================
    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal', $date);
    }

    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // ======================= Utilitas =======================
    public static function getDefaultJamKerja($shift)
    {
        $jamKerja = [
            'pagi'  => ['jam_masuk' => '07:00:00', 'jam_keluar' => '15:00:00'],
            'siang' => ['jam_masuk' => '15:00:00', 'jam_keluar' => '23:00:00'],
            'malam' => ['jam_masuk' => '23:00:00', 'jam_keluar' => '07:00:00'],
            'off'   => ['jam_masuk' => null,       'jam_keluar' => null],
        ];

        return $jamKerja[$shift] ?? ['jam_masuk' => null, 'jam_keluar' => null];
    }
    public static function getAvailableOperators($tanggal, $shift, $filters = [])
    {
        $query = self::whereDate('tanggal', $tanggal)
            ->where('shift', $shift)
            ->where('status', 'aktif')
            ->with('user');

        // Filter berdasarkan grup jika ada
        if (isset($filters['grup_id'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('grup_id', $filters['grup_id']);
            });
        }

        // Filter berdasarkan bagian jika ada
        if (isset($filters['bagian'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('bagian', $filters['bagian']);
            });
        }

        // Filter berdasarkan role jika ada
        if (isset($filters['role'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('role', $filters['role']);
            });
        }

        return $query->get()
            ->pluck('user')
            ->filter() // hilangkan jadwal tanpa user
            ->unique('id') // jangan duplikat
            ->values();
    }

    // ======================= Accessor =======================
    public function getFormattedJamMasukAttribute(): string
    {
        if (!$this->jam_masuk) return '-';

        try {
            return Carbon::createFromFormat('H:i:s', $this->jam_masuk)->format('H:i');
        } catch (\Exception $e) {
            return Carbon::createFromFormat('H:i', $this->jam_masuk)->format('H:i');
        }
    }

    public function getFormattedJamKeluarAttribute(): string
    {
        if (!$this->jam_keluar) return '-';

        try {
            return Carbon::createFromFormat('H:i:s', $this->jam_keluar)->format('H:i');
        } catch (\Exception $e) {
            return Carbon::createFromFormat('H:i', $this->jam_keluar)->format('H:i');
        }
    }

    public function getDurasiKerjaAttribute(): int
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return 0;
        }

        try {
            $masuk = Carbon::createFromFormat('H:i:s', $this->jam_masuk);
        } catch (\Exception $e) {
            $masuk = Carbon::createFromFormat('H:i', $this->jam_masuk);
        }

        try {
            $keluar = Carbon::createFromFormat('H:i:s', $this->jam_keluar);
        } catch (\Exception $e) {
            $keluar = Carbon::createFromFormat('H:i', $this->jam_keluar);
        }

        if ($keluar->lessThan($masuk)) {
            $keluar->addDay();
        }

        return $masuk->diffInHours($keluar);
    }

    // ======================= Mutator =======================
    public function setJamMasukAttribute($value)
    {
        $this->attributes['jam_masuk'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function setJamKeluarAttribute($value)
    {
        $this->attributes['jam_keluar'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }
    public function planningProduksi()
    {
        return $this->hasMany(PlanningProduksi::class, 'jadwal_kerja_id');
    }
}
