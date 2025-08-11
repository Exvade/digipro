<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OeeTarget extends Model
{
    use HasFactory;

    protected $table = 'oee_targets';

    protected $fillable = [
        'mesin_id',
        'target_oee',
        'target_availability',
        'target_performance',
        'target_quality',
        'target_cycle_time',
        'max_downtime_per_day',
        'max_stops_per_day',
        'period_type',
        'effective_date',
        'expiry_date',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'target_oee' => 'decimal:2',
        'target_availability' => 'decimal:2',
        'target_performance' => 'decimal:2',
        'target_quality' => 'decimal:2',
        'target_cycle_time' => 'integer',
        'max_downtime_per_day' => 'integer',
        'max_stops_per_day' => 'integer',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function mesin()
    {
        return $this->belongsTo(Mesin::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByMesin($query, $mesinId)
    {
        return $query->where('mesin_id', $mesinId);
    }

    public function scopeByPeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        return $query->where('effective_date', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', $date);
                    });
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsEffectiveAttribute()
    {
        $today = now()->format('Y-m-d');
        return $this->effective_date <= $today && 
               (!$this->expiry_date || $this->expiry_date >= $today);
    }

    public function getStatusColorAttribute()
    {
        if (!$this->is_active) return 'secondary';
        if ($this->is_expired) return 'danger';
        if ($this->is_effective) return 'success';
        return 'warning';
    }
} 