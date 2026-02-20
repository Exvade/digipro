<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $table = 'parts';

    protected $fillable = [
        'no_parts',
        'no_barang',
        'nama_part',
        'type',
        'quantity',
        'bruto',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }
    public function bomDetails()
    {
        return $this->hasMany(BomDetail::class);
    }
}
