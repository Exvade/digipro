<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    use HasFactory;

    protected $table = 'mesin';

    protected $fillable = [
        'nama_mesin',
        'brand_mesin',
        'tonase_mesin',
        'status',
        'stop_reason',
        'status_since',
        'keterangan',

        'bagian',
    ];

    protected $casts = [
        'tonase_mesin' => 'integer',

        'status_since' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }

    // OEE Relationships - dihapus karena tabel oee_records tidak digunakan
    // public function oeeData()
    // {
    //     return $this->hasMany(OeeRecord::class, 'mesin_id');
    // }

    public function downtimeEvents()
    {
        return $this->hasMany(DowntimeEvent::class);
    }

    public function qualityEvents()
    {
        return $this->hasMany(QualityEvent::class);
    }

    // HAPUS DUPLICATE - hanya pakai satu documents() method
    public function documents()
    {
        return $this->hasMany(MesinDocument::class);
    }

    public function activeDocuments()
    {
        return $this->hasMany(MesinDocument::class)->where('enabled', true);
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

    // Scopes
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeStop($query)
    {
        return $query->where('status', 'stop');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    // Accessors


    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'running':
                return 'success';
            case 'maintenance':
                return 'warning';
            case 'offline':
                return 'danger';
            case 'stop':
                return 'info';
            default:
                return 'secondary';
        }
    }

    // Methods berikut dihapus karena tabel oee_records tidak digunakan
    // Dashboard OEE menghitung data secara real-time dari planning_produksi dan input_produksi
    // 
    // public function getCurrentOeeData()
    // public function getLatestOeeData($days = 30)
    // public function getOeeDataForDate($date)
    // public function getOeeDataForPeriod($startDate, $endDate)
    // public function getAverageOee($days = 30)
    // public function getAverageAvailability($days = 30)
    // public function getAveragePerformance($days = 30)
    // public function getAverageQuality($days = 30)

    // Relationship to Planning Produksi
    public function plannings()
    {
        return $this->hasMany(PlanningProduksi::class, 'mesin_id');
    }

    // Get upcoming plannings (today and future)
    public function upcomingPlannings()
    {
        return $this->plannings()
            ->where('tanggal', '>=', now()->format('Y-m-d'))
            ->with('part')
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');
    }

    // Get plannings for today
    public function todayPlannings()
    {
        return $this->plannings()
            ->where('tanggal', now()->format('Y-m-d'))
            ->with('part')
            ->orderBy('id', 'asc');
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
