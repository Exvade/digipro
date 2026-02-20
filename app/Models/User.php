<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'username',
        'nik',
        'departemen',
        'email',
        'password',
        'role',
        'bagian',
        'grup_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Override username untuk Laravel's built-in auth
     */
    public function username()
    {
        return 'nik';
    }

    /**
     * Find user by NIK or Username for authentication
     */
    public static function findForAuth($login)
    {
        return self::where(function ($query) use ($login) {
            $query->where('nik', $login)
                ->orWhere('username', $login);
        })->where('is_active', true)->first();
    }

    // ... rest of your existing methods remain the same ...

    public function leadingGrup()
    {
        return $this->hasOne(Grup::class, 'leader_id');
    }

    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }

    public function approvedChecksheets()
    {
        return $this->hasMany(Checksheet::class, 'approved_by');
    }

    public function scopeGrup($query, $grup)
    {
        return $query->where('grup', $grup);
    }

    public function scopeLeaders($query)
    {
        return $query->where('role', 'leader');
    }

    public function scopeOperators($query)
    {
        return $query->where('role', 'operator');
    }

    public function scopeBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    public function isLeader()
    {
        return $this->role === 'leader';
    }

    public function isOperator()
    {
        return $this->role === 'operator';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function getGroupInfoAttribute()
    {
        $info = [];

        if ($this->bagian) {
            $info[] = "Bagian: " . ucfirst($this->bagian);
        }

        if ($this->grup) {
            $info[] = "Grup: {$this->grup}";
        }

        return implode(' | ', $info);
    }

    public function getDisplayNameAttribute()
    {
        $display = $this->nama;

        if ($this->grup) {
            $display .= " (Grup {$this->grup})";
        }

        return $display;
    }

    public function grup()
    {
        return $this->belongsTo(\App\Models\Grup::class, 'grup_id');
    }

    public function jadwalKerjas()
    {
        return $this->hasMany(\App\Models\JadwalKerja::class, 'user_id');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getRoleDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->role));
    }

    public function getRoleBadgeColorAttribute()
    {
        switch ($this->role) {
            case 'super_admin':
                return 'danger';
            case 'supervisor':
                return 'warning';
            case 'maintenance':
                return 'danger';
            case 'foreman':
                return 'info';
            case 'leader':
                return 'primary';
            case 'kabag':
                return 'dark';
            case 'manager_plant':
                return 'secondary';
            default:
                return 'info';
        }
    }

    public function canBeDeleted()
    {
        return $this->id !== auth()->id();
    }

    public function getLastLoginHumanAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Belum pernah login';
    }
}
