<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MejaDocument extends Model
{
    use HasFactory;

    protected $table = 'meja_documents';

    protected $fillable = [
        'meja_id',
        'type',
        'url',
        'filename',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'id_meja');
    }
}