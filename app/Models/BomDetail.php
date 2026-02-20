<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{
    use HasFactory;

    protected $table = 'bom_details';

    protected $fillable = [
        'moldshop_id',
        'part_id',
        'cavity_mold',
        'cycle_time_std',
        'resin_type',
        'shot_weight',
        'remarks',
    ];

    // Relasi ke Moldshop
    public function moldshop()
    {
        return $this->belongsTo(Moldshop::class);
    }

    // Relasi ke Part
    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
