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
        'shift',
        'approval_status',
        'meja_id',
        'apd_topi',
        'apd_sepatu',
        'quality_qpoint',
        'instruksi_kerja',
        'limit_sampel',
        'amptm_standar',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
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
} 