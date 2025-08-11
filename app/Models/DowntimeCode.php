<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DowntimeCode extends Model
{
    use HasFactory;

    protected $table = 'downtime_codes';

    protected $fillable = [
        'downtime_category_id',
        'name',
        'code',
        'description',
        'type',
        'responsibility',
        'requires_approval',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(DowntimeCategory::class, 'downtime_category_id');
    }

    public function downtimeEvents()
    {
        return $this->hasMany(DowntimeEvent::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByResponsibility($query, $responsibility)
    {
        return $query->where('responsibility', $responsibility);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
} 