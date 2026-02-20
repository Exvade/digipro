<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InputProduksi extends Model
{
    use HasFactory;

    protected $table = 'input_produksi';

    protected $fillable = [
        'planning_id',
        'ploting_id',   
        'ok_count',
        'ng_count',
        'ng_material',   
        'ng_process',    
        'ng_machine',    
        'ng_operator',   
        'ng_other',      
        'ng_description', // Digunakan untuk rejection reason
        // Kolom berikut dihapus karena tidak digunakan:
        // 'ng_category',
        // 'ng_code',
        // 'ng_category_id',
        // 'ng_code_id',
        // 'corrective_action',
        // 'preventive_action',
        // 'ng_status',
        // 'assigned_to',
        // 'resolved_at',

        // 🆕 Tambahan untuk sistem Approval Leader
        'status',       // pending, approved, rejected
        'submitted_at', // waktu submit
        'user_id',      // siapa yang approve/reject (leader/super_admin)
        'approved_at',  // waktu approve
        'rejected_at',  // waktu reject
        'operator_signature_qr', // QR code tanda tangan operator
        'leader_signature_qr',   // QR code tanda tangan leader
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        // 'resolved_at' => 'datetime', // Dihapus karena kolom tidak digunakan
    ];

    /* ===================== RELASI ===================== */

    public function planning()
    {
        return $this->belongsTo(PlanningProduksi::class, 'planning_id');
    }

    public function ploting()
    {
        return $this->belongsTo(Ploting::class, 'ploting_id');
    }

    /**
     * Relasi dengan NG Part Types (many-to-many)
     */
    public function ngPartTypes()
    {
        return $this->belongsToMany(NgPartType::class, 'input_produksi_ng_parts')
            ->withPivot('quantity', 'category')
            ->withTimestamps();
    }

    // Relasi ngDetails dihapus - tabel ng_details tidak digunakan
    // public function ngDetails()
    // {
    //     return $this->hasMany(NgDetail::class);
    // }

    // Relasi berikut dihapus karena kolom tidak digunakan:
    // public function ngCategory()
    // {
    //     return $this->belongsTo(DowntimeCategory::class, 'ng_category_id');
    // }
    // 
    // public function ngCode()
    // {
    //     return $this->belongsTo(DowntimeCode::class, 'ng_code_id');
    // }
    // 
    // public function assignedTo()
    // {
    //     return $this->belongsTo(User::class, 'assigned_to');
    // }

    /**
     * User yang melakukan approve/reject (leader/super_admin)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias untuk relasi user (untuk clarity)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias untuk relasi user (untuk clarity)
     */
    public function rejector()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ===================== SCOPES ===================== */

    // Scope withNgDetails dihapus - tabel ng_details tidak digunakan
    // public function scopeWithNgDetails($query)
    // {
    //     return $query->with(['ngDetails.downtimeCategory', 'ngDetails.downtimeCode']);
    // }

    // Scope berikut dihapus karena kolom ng_status tidak digunakan:
    // public function scopeByNgStatus($query, $status)
    // {
    //     return $query->where('ng_status', $status);
    // }
    // 
    // public function scopeOpenNg($query)
    // {
    //     return $query->whereIn('ng_status', ['open', 'investigating']);
    // }
    // 
    // public function scopeResolvedNg($query)
    // {
    //     return $query->whereIn('ng_status', ['resolved', 'closed']);
    // }

    // 🆕 Scope untuk sistem approval leader
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /* ===================== ACCESSORS ===================== */

    // Accessor berikut dihapus karena kolom ng_status tidak digunakan:
    // public function getNgStatusColorAttribute()
    // {
    //     switch ($this->ng_status) {
    //         case 'open':
    //             return 'danger';
    //         case 'investigating':
    //             return 'warning';
    //         case 'resolved':
    //             return 'success';
    //         case 'closed':
    //             return 'secondary';
    //         default:
    //             return 'info';
    //     }
    // }
    // 
    // public function getNgStatusLabelAttribute()
    // {
    //     switch ($this->ng_status) {
    //         case 'open':
    //             return 'Terbuka';
    //         case 'investigating':
    //             return 'Sedang Diteliti';
    //         case 'resolved':
    //             return 'Terselesaikan';
    //         case 'closed':
    //             return 'Ditutup';
    //         default:
    //             return 'Tidak Diketahui';
    //     }
    // }

    // 🆕 Accessor untuk status approval
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'warning';
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'Menunggu Approval';
            case 'approved':
                return 'Disetujui';
            case 'rejected':
                return 'Ditolak';
            default:
                return 'Tidak Diketahui';
        }
    }

    // Accessor getTotalNgDetailsAttribute dihapus - menggunakan kolom langsung
    // public function getTotalNgDetailsAttribute()
    // {
    //     return $this->ngDetails->sum('quantity');
    // }

    public function getTotalNgByCategoryAttribute()
    {
        return [
            'material' => $this->ng_material,
            'process' => $this->ng_process,
            'machine' => $this->ng_machine,
            'operator' => $this->ng_operator,
            'other' => $this->ng_other,
            'total' => $this->ng_material + $this->ng_process + $this->ng_machine + $this->ng_operator + $this->ng_other
        ];
    }

    public function getMainNgCategoryAttribute()
    {
        $categories = [
            'material' => $this->ng_material,
            'process' => $this->ng_process,
            'machine' => $this->ng_machine,
            'operator' => $this->ng_operator,
            'other' => $this->ng_other
        ];

        $maxCategory = array_keys($categories, max($categories))[0];
        return [
            'category' => $maxCategory,
            'count' => $categories[$maxCategory],
            'percentage' => $this->ng_count > 0 ? round(($categories[$maxCategory] / $this->ng_count) * 100, 2) : 0
        ];
    }
}