<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checksheet extends Model
{
    use HasFactory;

    protected $table = 'checksheets';

    protected $fillable = [
        'mesin_id',
        'user_id',
        'grup_id',
        'part_id',
        'approval_status',
        'meja_id',
        'apd_topi',
        'apd_sepatu',
        'quality_qpoint',
        'instruksi_kerja',
        'limit_sampel',
        'amptm_standar',
        'suhu_mesin',
        'tekanan_mesin',
        'getaran_mesin',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $appends = ['shift', 'shift_display'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function mesin()
    {
        return $this->belongsTo(Mesin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function photos()
    {
        return $this->hasMany(ChecksheetPhoto::class);
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function grup()
    {
        return $this->belongsTo(\App\Models\Grup::class, 'grup_id');
    }
    public function mesinDocuments()
    {
        return $this->hasMany(MesinDocument::class, 'mesin_id', 'mesin_id');
    }

    public function mejaDocuments()
    {
        return $this->hasMany(MejaDocument::class, 'meja_id', 'meja_id');
    }

    /**
     * Get shift from jadwal kerja for the checksheet date
     * Returns shift in format: shift_1, shift_2, shift_3
     * This is an accessor that replaces the database column
     */
    public function getShiftAttribute($value)
    {
        // If shift column still exists in database (for backward compatibility during migration)
        // Check if we have a database value first
        if (isset($this->attributes['shift']) && $this->attributes['shift']) {
            return $this->attributes['shift'];
        }

        // Otherwise, get from jadwal kerja
        if (!$this->user_id || !$this->created_at) {
            return null;
        }

        $checksheetDate = $this->created_at->format('Y-m-d');

        // Try to use eager-loaded relationship first
        if ($this->relationLoaded('user') && $this->user && $this->user->relationLoaded('jadwalKerjas')) {
            $jadwal = $this->user->jadwalKerjas->first(function ($j) use ($checksheetDate) {
                // Convert tanggal to Y-m-d format for comparison
                $jadwalDate = $j->tanggal instanceof \Carbon\Carbon
                    ? $j->tanggal->format('Y-m-d')
                    : (is_string($j->tanggal) ? $j->tanggal : date('Y-m-d', strtotime($j->tanggal)));

                return $jadwalDate === $checksheetDate && $j->status === 'aktif';
            });

            if ($jadwal) {
                // Map jadwal kerja shift to checksheet shift format
                $shiftMap = [
                    'pagi' => 'shift_1',
                    'siang' => 'shift_2',
                    'malam' => 'shift_3',
                    'off' => null
                ];
                return $shiftMap[$jadwal->shift] ?? $jadwal->shift;
            }
        } else {
            // Fallback: query database if relationship not loaded
            $jadwal = \App\Models\JadwalKerja::where('user_id', $this->user_id)
                ->whereDate('tanggal', $checksheetDate)
                ->where('status', 'aktif')
                ->first();

            if ($jadwal) {
                // Map jadwal kerja shift to checksheet shift format
                $shiftMap = [
                    'pagi' => 'shift_1',
                    'siang' => 'shift_2',
                    'malam' => 'shift_3',
                    'off' => null
                ];
                return $shiftMap[$jadwal->shift] ?? $jadwal->shift;
            }
        }

        // Fallback: get shift from grup if available
        if ($this->user && $this->user->grup && $this->user->grup->shift && $this->user->grup->shift !== 'all') {
            $shiftMap = [
                'pagi' => 'shift_1',
                'siang' => 'shift_2',
                'malam' => 'shift_3'
            ];
            return $shiftMap[$this->user->grup->shift] ?? $this->user->grup->shift;
        }

        return null;
    }

    /**
     * Get shift display name (for backward compatibility)
     */
    public function getShiftDisplayAttribute()
    {
        $shift = $this->shift;
        if (!$shift) {
            return '-';
        }

        $displayMap = [
            'shift_1' => 'Shift 1 (Pagi)',
            'shift_2' => 'Shift 2 (Siang)',
            'shift_3' => 'Shift 3 (Malam)'
        ];

        return $displayMap[$shift] ?? ucfirst(str_replace('_', ' ', $shift));
    }
}
