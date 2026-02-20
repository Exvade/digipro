<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiPerJam extends Model
{
    use HasFactory;

    protected $table = 'produksi_per_jam';

    protected $fillable = [
        'planning_id',
        'input_produksi_id',
        'ploting_id',
        'shift',
        'jam',
        'target',
        'ok_count',
        'ng_part',
        'operator_code',
        'ng_subpart_1',
        'ng_subpart_2',
        'ng_subpart_3',
        'ng_subpart_4',
        'ng_subpart_5',
        'ng_subpart_6',
        'ng_subpart_7',
        'masalah',
        'tindakan',
        'checker_prd',
        'checker_qc',
    ];

    protected $casts = [
        'target' => 'integer',
        'ok_count' => 'integer',
        'ng_part' => 'integer',
        'ng_subpart_1' => 'integer',
        'ng_subpart_2' => 'integer',
        'ng_subpart_3' => 'integer',
        'ng_subpart_4' => 'integer',
        'ng_subpart_5' => 'integer',
        'ng_subpart_6' => 'integer',
        'ng_subpart_7' => 'integer',
    ];

    /* ===================== RELASI ===================== */

    public function planning()
    {
        return $this->belongsTo(PlanningProduksi::class, 'planning_id');
    }

    public function inputProduksi()
    {
        return $this->belongsTo(InputProduksi::class, 'input_produksi_id');
    }

    public function ploting()
    {
        return $this->belongsTo(Ploting::class, 'ploting_id');
    }

    /* ===================== SCOPES ===================== */

    public function scopeByPlanning($query, $planningId)
    {
        return $query->where('planning_id', $planningId);
    }

    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    public function scopeByJam($query, $jam)
    {
        return $query->where('jam', $jam);
    }

    /* ===================== ACCESSORS ===================== */

    public function getTotalNgSubpartAttribute()
    {
        return $this->ng_subpart_1 + $this->ng_subpart_2 + $this->ng_subpart_3 + 
               $this->ng_subpart_4 + $this->ng_subpart_5 + $this->ng_subpart_6 + $this->ng_subpart_7;
    }

    public function getTotalOutputAttribute()
    {
        return $this->ok_count + $this->ng_part;
    }
}
