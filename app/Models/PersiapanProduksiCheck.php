<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersiapanProduksiCheck extends Model
{
    use HasFactory;

    protected $table = 'persiapan_produksi_checks';

    protected $fillable = [
        'ploting_id',
        'item_name',
        'operator_checked',
        'leader_checked',
        'keterangan',
    ];

    protected $casts = [
        'operator_checked' => 'boolean',
        'leader_checked' => 'boolean',
    ];

    public function ploting()
    {
        return $this->belongsTo(Ploting::class, 'ploting_id');
    }
}
