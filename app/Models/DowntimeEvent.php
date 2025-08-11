<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DowntimeEvent extends Model
{
    use HasFactory;

    protected $table = 'downtime_events';

    protected $fillable = [
        'mesin_id',
        'user_id',
        'oee_data_id',
        'tanggal',
        'shift',
        'start_time',
        'end_time',
        'duration_minutes',
        'downtime_category_id',
        'downtime_code_id',
        'downtime_type',
        'responsibility',
        'title',
        'description',
        'root_cause',
        'corrective_action',
        'status',
        'resolved_by',
        'resolved_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'duration_minutes' => 'integer',
        'resolved_at' => 'datetime',
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

    public function oeeData()
    {
        return $this->belongsTo(OeeData::class);
    }

    public function category()
    {
        return $this->belongsTo(DowntimeCategory::class, 'downtime_category_id');
    }

    public function code()
    {
        return $this->belongsTo(DowntimeCode::class, 'downtime_code_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
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

    public function scopeByType($query, $type)
    {
        return $query->where('downtime_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    // Accessors
    public function getDurationHoursAttribute()
    {
        return $this->duration_minutes ? round($this->duration_minutes / 60, 2) : 0;
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'active':
                return 'danger';
            case 'resolved':
                return 'success';
            case 'pending':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    public function getTypeColorAttribute()
    {
        return $this->downtime_type === 'planned' ? 'warning' : 'danger';
    }
} 