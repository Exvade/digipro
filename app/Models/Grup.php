<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grup extends Model
{
    use HasFactory;

    protected $table = 'grups';

    protected $fillable = [
        'nama_grup',
        'bagian',
        'shift',
        'area',
        'deskripsi',
        'status',
        'leader_id'
    ];

    /**
     * Get the leader for this grup.
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the users for this grup.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'grup_id');
    }

    /**
     * Get the operators for this grup.
     */
    public function operators()
    {
        return $this->hasMany(User::class, 'grup_id')->where('role', 'operator');
    }

    /**
     * Get the checksheets for this grup.
     */
    public function checksheets()
    {
        return $this->hasManyThrough(Checksheet::class, User::class, 'grup_id', 'user_id');
    }

    /**
     * Scope untuk grup aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk grup berdasarkan bagian
     */
    public function scopeBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    /**
     * Scope untuk grup berdasarkan shift
     */
    public function scopeShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    /**
     * Scope untuk grup berdasarkan area
     */
    public function scopeArea($query, $area)
    {
        return $query->where('area', $area);
    }

    /**
     * Get full grup name with details
     */
    public function getFullNameAttribute()
    {
        $name = $this->nama_grup;
        
        if ($this->shift !== 'all') {
            $name .= " (Shift " . ucfirst($this->shift) . ")";
        }
        
        if ($this->area) {
            $name .= " - " . $this->area;
        }
        
        return $name;
    }

    /**
     * Get grup info for display
     */
    public function getInfoAttribute()
    {
        $info = [];
        $info[] = "Bagian: " . ucfirst($this->bagian);
        $info[] = "Shift: " . ucfirst($this->shift);
        
        if ($this->area) {
            $info[] = "Area: " . $this->area;
        }
        
        if ($this->leader) {
            $info[] = "Leader: " . $this->leader->nama;
        }
        
        return implode(' | ', $info);
    }
} 