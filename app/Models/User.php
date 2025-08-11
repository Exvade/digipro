<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
     * Get the grup that this user leads.
     */
    public function leadingGrup()
    {
        return $this->hasOne(Grup::class, 'leader_id');
    }

    /**
     * Get the checksheets for the user.
     */
    public function checksheets()
    {
        return $this->hasMany(Checksheet::class);
    }

    /**
     * Get the approved checksheets for the user.
     */
    public function approvedChecksheets()
    {
        return $this->hasMany(Checksheet::class, 'approved_by');
    }

    /**
     * Scope untuk user berdasarkan grup
     */
    public function scopeGrup($query, $grup)
    {
        return $query->where('grup', $grup);
    }

    /**
     * Scope untuk leaders
     */
    public function scopeLeaders($query)
    {
        return $query->where('role', 'leader');
    }

    /**
     * Scope untuk operators
     */
    public function scopeOperators($query)
    {
        return $query->where('role', 'operator');
    }

    /**
     * Scope untuk user berdasarkan bagian
     */
    public function scopeBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    /**
     * Check if user is a leader
     */
    public function isLeader()
    {
        return $this->role === 'leader';
    }

    /**
     * Check if user is an operator
     */
    public function isOperator()
    {
        return $this->role === 'operator';
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Get user's full group information
     */
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

    /**
     * Get user's display name with role and group
     */
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

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    /**
     * Get user status color
     */
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->role));
    }

    /**
     * Get role badge color
     */
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

    /**
     * Check if user can be deleted
     */
    public function canBeDeleted()
    {
        return $this->id !== auth()->id();
    }

    /**
     * Get user's last login in human readable format
     */
    public function getLastLoginHumanAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Belum pernah login';
    }
}
