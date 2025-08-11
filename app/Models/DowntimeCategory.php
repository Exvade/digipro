<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DowntimeCategory extends Model
{
    use HasFactory;

    protected $table = 'downtime_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'color',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function downtimeCodes()
    {
        return $this->hasMany(DowntimeCode::class);
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
} 