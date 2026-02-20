<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moldshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_mold',
        'nama_mold',
        'type',
        'customer',
        'status', // ditambahkan agar bisa diisi massal
    ];
    public function bomDetails()
    {
        return $this->hasMany(BomDetail::class);
    }

    public function plannings()
    {
        return $this->hasMany(PlanningProduksi::class, 'moldshop_id');
    }

    public function inputProduksi()
    {
        return $this->hasManyThrough(
            InputProduksi::class,
            PlanningProduksi::class,
            'moldshop_id', // Foreign key on planning_produksi table...
            'planning_id', // Foreign key on input_produksi table...
            'id',          // Local key on moldshops table...
            'id'           // Local key on planning_produksi table...
        );
    }
}
