<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    use HasFactory;

    protected $table = 'mesin';

    protected $fillable = [
        'nama_mesin',
        'brand_mesin',
        'tonase_mesin',
        'status',
        'maintenance_count',
        'offline_count',
    ];

    protected $casts = [
        'tonase_mesin' => 'integer',
        'maintenance_count' => 'integer',
        'offline_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }

    // OEE Relationships
    public function oeeData()
    {
        return $this->hasMany(OeeData::class);
    }

    public function downtimeEvents()
    {
        return $this->hasMany(DowntimeEvent::class);
    }

    public function qualityEvents()
    {
        return $this->hasMany(QualityEvent::class);
    }

    public function oeeTargets()
    {
        return $this->hasMany(OeeTarget::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'tidak aktif');
    }

    // Accessors
    public function getMaintenanceCountAttribute($value)
    {
        return $value ?? 0;
    }

    public function getOfflineCountAttribute($value)
    {
        return $value ?? 0;
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'aktif':
                return 'success';
            case 'maintenance':
                return 'warning';
            case 'tidak aktif':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    // Methods
    public function getCurrentOeeData()
    {
        return $this->oeeData()
                    ->where('tanggal', now()->format('Y-m-d'))
                    ->first();
    }

    public function getCurrentTarget()
    {
        return $this->oeeTargets()
                    ->active()
                    ->effective()
                    ->first();
    }

    public function getLatestOeeData($days = 30)
    {
        return $this->oeeData()
                    ->where('tanggal', '>=', now()->subDays($days))
                    ->orderBy('tanggal', 'desc')
                    ->get();
    }

    // Additional methods for OEE data access
    public function getOeeDataForDate($date)
    {
        return $this->oeeData()
                    ->where('tanggal', $date)
                    ->first();
    }

    public function getOeeDataForPeriod($startDate, $endDate)
    {
        return $this->oeeData()
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->orderBy('tanggal', 'desc')
                    ->get();
    }

    public function getAverageOee($days = 30)
    {
        return $this->oeeData()
                    ->where('tanggal', '>=', now()->subDays($days))
                    ->avg('oee') ?? 0;
    }

    public function getAverageAvailability($days = 30)
    {
        return $this->oeeData()
                    ->where('tanggal', '>=', now()->subDays($days))
                    ->avg('availability') ?? 0;
    }

    public function getAveragePerformance($days = 30)
    {
        return $this->oeeData()
                    ->where('tanggal', '>=', now()->subDays($days))
                    ->avg('performance') ?? 0;
    }

    public function getAverageQuality($days = 30)
    {
        return $this->oeeData()
                    ->where('tanggal', '>=', now()->subDays($days))
                    ->avg('quality') ?? 0;
    }
} 