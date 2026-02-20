<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgPartType extends Model
{
    use HasFactory;

    protected $table = 'ng_part_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /* ===================== RELASI ===================== */

    public function inputProduksi()
    {
        return $this->belongsToMany(InputProduksi::class, 'input_produksi_ng_parts')
            ->withPivot('quantity', 'category')
            ->withTimestamps();
    }

    /* ===================== SCOPES ===================== */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
