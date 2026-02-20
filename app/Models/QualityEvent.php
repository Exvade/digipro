<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityEvent extends Model
{
    use HasFactory;

    protected $table = 'quality_events';

    protected $fillable = [
        'mesin_id',
        'user_id',
        'oee_data_id',
        'tanggal',
        'shift',
        'time_occurred',
        'defect_type',
        'defect_code',
        'defect_description',
        'quantity_defective',
        'quantity_checked',
        'root_cause_category',
        'root_cause_description',
        'corrective_action',
        'preventive_action',
        'cost_per_unit',
        'total_cost',
        'status',
        'assigned_to',
        'resolved_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'time_occurred' => 'datetime:H:i:s',
        'quantity_defective' => 'integer',
        'quantity_checked' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
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
        // return $this->belongsTo(OeeData::class);
        return null;
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    public function scopeByDefectType($query, $defectType)
    {
        return $query->where('defect_type', $defectType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'investigating']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    // Accessors
    public function getDefectRateAttribute()
    {
        if ($this->quantity_checked > 0) {
            return round(($this->quantity_defective / $this->quantity_checked) * 100, 2);
        }
        return 0;
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'open':
                return 'danger';
            case 'investigating':
                return 'warning';
            case 'resolved':
                return 'success';
            case 'closed':
                return 'secondary';
            default:
                return 'info';
        }
    }

    public function getDefectTypeColorAttribute()
    {
        switch ($this->defect_type) {
            case 'visual':
                return 'info';
            case 'dimensional':
                return 'warning';
            case 'functional':
                return 'danger';
            case 'material':
                return 'primary';
            default:
                return 'secondary';
        }
    }
}
