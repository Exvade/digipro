<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningProduksiJam extends Model
{
    use HasFactory;

    protected $table = 'planning_produksi_jam';

    protected $fillable = [
        'planning_id',
        'shift',
        'jam',
        'target',
    ];

    public function planning()
    {
        return $this->belongsTo(PlanningProduksi::class, 'planning_id');
    }
}
