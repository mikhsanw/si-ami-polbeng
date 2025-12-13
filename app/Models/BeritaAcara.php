<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaAcara extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'audit_periode_id', 'catatan',
    ];

    public function auditPeriode()
    {
        return $this->belongsTo('App\Models\AuditPeriode');
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
