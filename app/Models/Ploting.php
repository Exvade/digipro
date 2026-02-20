<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ploting extends Model
{
    use HasFactory;

    protected $table = 'ploting_produksi';
    protected $fillable = [
        'planning_id',
        'grup_id',
        'user_id',
        'status',
        'start_time',
        'end_time',
        'run_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'run_time'   => 'integer',
    ];

    public function planning()
    {
        return $this->belongsTo(PlanningProduksi::class, 'planning_id');
    }

    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    // Relasi ke Part
    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id');
    }

    public function grup()
    {
        return $this->belongsTo(Grup::class, 'grup_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias untuk operator (untuk kompatibilitas)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inputProduksi()
    {
        return $this->hasOne(InputProduksi::class, 'ploting_id', 'id');
    }

    public function persiapanChecks()
    {
        return $this->hasMany(PersiapanProduksiCheck::class, 'ploting_id');
    }
}
