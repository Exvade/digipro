<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OeeData extends Model
{
    use HasFactory;

    protected $table = 'oee_data';

    protected $fillable = [
        'mesin_id',
        'user_id',
        'tanggal',
        'shift',
        'start_time',
        'end_time',
        'planned_production_time',
        'actual_running_time',
        'total_downtime',
        'availability',
        'ideal_cycle_time',
        'actual_cycle_time',
        'total_units_produced',
        'good_units_produced',
        'rejected_units',
        'performance',
        'quality',
        'oee',
        'stop_count',
        'setup_time',
        'changeover_time',
        'notes'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'availability' => 'decimal:2',
        'performance' => 'decimal:2',
        'quality' => 'decimal:2',
        'oee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function downtimeEvents()
    {
        return $this->hasMany(DowntimeEvent::class);
    }

    public function qualityEvents()
    {
        return $this->hasMany(QualityEvent::class);
    }

    // Scopes
    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal', $date);
    }

    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    public function scopeByMesin($query, $mesinId)
    {
        return $query->where('mesin_id', $mesinId);
    }

    // Accessors
    public function getOeeColorAttribute()
    {
        if ($this->oee >= 85) return 'success';
        if ($this->oee >= 70) return 'warning';
        return 'danger';
    }

    public function getAvailabilityColorAttribute()
    {
        if ($this->availability >= 90) return 'success';
        if ($this->availability >= 80) return 'warning';
        return 'danger';
    }

    public function getPerformanceColorAttribute()
    {
        if ($this->performance >= 95) return 'success';
        if ($this->performance >= 85) return 'warning';
        return 'danger';
    }

    public function getQualityColorAttribute()
    {
        if ($this->quality >= 99) return 'success';
        if ($this->quality >= 95) return 'warning';
        return 'danger';
    }
} 