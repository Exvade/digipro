<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'meja';
    protected $primaryKey = 'id_meja';

    protected $fillable = [
        'nama_meja',
        'lokasi',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }

    // HAPUS DUPLICATE INI - hanya pakai satu documents() method
    public function documents()
    {
        return $this->hasMany(MejaDocument::class, 'meja_id', 'id_meja');
    }

    public function activeDocuments()
    {
        return $this->hasMany(MejaDocument::class, 'meja_id', 'id_meja')->where('enabled', true);
    }

    // Helper untuk get dokumen by type
    public function getDocument($type)
    {
        return $this->documents()
            ->where('type', $type)
            ->where('enabled', true)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Cek dokumen apa saja yang belum diupload
     */
    public function getMissingDocuments()
    {
        $requiredTypes = [
            'apd' => 'APD',
            'qpoint' => 'Q-Point',
            'instruksi_kerja' => 'Instruksi Kerja',
            'limit_sampel' => 'Limit Sampel',
            'amptm' => 'AMTPM'
        ];

        $missing = [];
        foreach ($requiredTypes as $type => $label) {
            if (!$this->getDocument($type)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Cek apakah semua dokumen wajib sudah diupload
     */
    public function hasCompleteDocuments()
    {
        return count($this->getMissingDocuments()) === 0;
    }
}
