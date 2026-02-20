<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MesinDocument extends Model
{
    use HasFactory;

    protected $table = 'mesin_documents';

    protected $fillable = [
        'mesin_id',
        'type',
        'url',
        'filename',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function mesin()
    {
        return $this->belongsTo(Mesin::class);
    }
}