<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPemeliharaan extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_pemeliharaans';
    protected $fillable = [
        'mesin_id', 'tahun', 'bulan', 'minggu', 'plan_tipe', 'aktual_tanggal', 'keterangan'
    ];

    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}
