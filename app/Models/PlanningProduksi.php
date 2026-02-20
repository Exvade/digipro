<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningProduksi extends Model
{
    use HasFactory;

    protected $table = 'planning_produksi';

    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at',
        'signed_at',
        'approved_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $fillable = [
        'moldshop_id',
        'mesin_id',
        'meja_id',
        'bagian',
        'part_id',
        'jadwal_kerja_id',
        'tanggal',
        'lot_produksi',
        'qty_planning_produksi',
        'shift',
        'target_shift_pagi',
        'target_shift_siang',
        'target_shift_malam',
        'total_shift',
        'catatan',
        'signature_qr_code',
        'approval_qr_code',
        'signed_by',
        'signed_at',
        'status_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI
    |--------------------------------------------------------------------------
    */

    // 🔹 Relasi ke Moldshop
    public function moldshop()
    {
        return $this->belongsTo(Moldshop::class);
    }

    // 🔹 Relasi ke Mesin
    public function mesin()
    {
        return $this->belongsTo(Mesin::class);
    }

    // 🔹 Relasi ke Meja (untuk Assembly)
    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'id_meja');
    }

    // 🔹 Relasi ke Part
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    // 🔹 Relasi ke Jadwal Kerja
    public function jadwalKerja()
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    // 🔹 Relasi ke Input Produksi
    public function inputProduksi()
    {
        return $this->hasOne(InputProduksi::class, 'planning_id');
    }

    // 🔹 Relasi ke Ploting Produksi
    public function plotings()
    {
        return $this->hasMany(Ploting::class, 'planning_id');
    }

    // 🔹 Relasi baru ke tabel jam (1 Planning punya banyak jam)
    public function jam()
    {
        return $this->hasMany(PlanningProduksiJam::class, 'planning_id');
    }
    public function bomDetail()
    {
        return $this->hasOne(BomDetail::class, 'moldshop_id', 'moldshop_id')
            ->where('part_id', $this->part_id);
    }

    // 🔹 Relasi ke User yang approve (Kabag PPIC)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
