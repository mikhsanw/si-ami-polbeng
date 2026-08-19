<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, softDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'detail',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'id' => 'string',
        'detail' => 'array',
    ];

    protected $primaryKey = 'id';

    public function penugasanAuditors()
    {
        return $this->hasMany(\App\Models\PenugasanAuditor::class);
    }
    public function unit()
    {
        return $this->hasOne(\App\Models\Unit::class);
    }

    /**
     * Get list of unit IDs that this user is allowed to monitor.
     * Returns null if user can monitor all units globally (Super Admin, Admin, Direktur).
     * Returns array of unit IDs (Jurusan + children prodi) for GKM.
     * Returns array of single unit ID or empty array for Auditee/Auditor.
     *
     * @return array|null
     */
    public function getMonitoredUnitIds(): ?array
    {
        if ($this->hasRole(['Super Admin', 'Admin', 'Direktur'])) {
            return null;
        }

        $userUnit = $this->unit;

        if ($this->hasRole('GKM')) {
            if (!$userUnit) {
                return [];
            }

            return \App\Models\Unit::where('id', $userUnit->id)
                ->orWhere('parent_id', $userUnit->id)
                ->pluck('id')
                ->toArray();
        }

        return $userUnit ? [$userUnit->id] : [];
    }
}
