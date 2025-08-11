<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecksheetPhoto extends Model
{
    use HasFactory;

    protected $table = 'checksheet_photos';

    protected $fillable = [
        'checksheet_id',
        'file_path',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the checksheet that owns the photo.
     */
    public function checksheet()
    {
        return $this->belongsTo(Checksheet::class);
    }

    /**
     * Get the type options for photos.
     */
    public static function getTypeOptions()
    {
        return [
            'apd' => 'APD',
            'instruksi_kerja' => 'Instruksi Kerja',
            'quality' => 'Q-Point',
            'qpoint' => 'Q-Point',
            'limit_sampel' => 'Limit Sampel',
            'amptm' => 'Standar AMTPM'
        ];
    }

    /**
     * Get the display name for the photo type.
     */
    public function getTypeDisplayNameAttribute()
    {
        $options = self::getTypeOptions();
        return $options[$this->type] ?? $this->type;
    }

    // Accessor for full URL
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
} 