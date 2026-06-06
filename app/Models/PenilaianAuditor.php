<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenilaianAuditor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id', 'auditor_id', 'penilai_id', 'audit_periode_id',
        'pct_responsivitas', 'avg_hari_respon', 'pct_kecepatan', 'pct_catatan',
        'skor_keseluruhan', 'catatan',
    ];

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    public function auditPeriode()
    {
        return $this->belongsTo(AuditPeriode::class, 'audit_periode_id');
    }

    public function getLabelSkorAttribute(): string
    {
        $s = $this->skor_keseluruhan ?? 0;
        if ($s >= 80) return 'Sangat Baik';
        if ($s >= 60) return 'Baik';
        if ($s >= 40) return 'Cukup';
        return 'Perlu Perhatian';
    }

    public function getWarnaSkorAttribute(): string
    {
        $s = $this->skor_keseluruhan ?? 0;
        if ($s >= 80) return 'success';
        if ($s >= 60) return 'primary';
        if ($s >= 40) return 'warning';
        return 'danger';
    }
}
