<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indikator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'nama', 'tipe', 'kriteria_id',
    ];

    public function kriteria()
    {
        return $this->belongsTo('App\Models\Kriteria');
    }

    public function rubrikPenilaians()
    {
        return $this->hasMany('App\Models\RubrikPenilaian');
    }

    public function indikatorInputs()
    {
        return $this->hasMany('App\Models\IndikatorInput');
    }

    public function hasilAudits()
    {
        return $this->hasMany('App\Models\HasilAudit');
    }

    public function hasilAudit()
    {
        return $this->hasOne('App\Models\HasilAudit');
    }

    public function templateIndikators()
    {
        return $this->hasMany('App\Models\TemplateIndikator');
    }

    public function hasilAuditForPeriode($auditPeriodeId)
    {
        return $this->hasilAudits()
            ->where('audit_periode_id', $auditPeriodeId)
            ->first();
    }

    public function hasilAuditForPeriodes($auditPeriodeId)
    {
        return $this->hasilAudits()->where('audit_periode_id', $auditPeriodeId)->get();
    }
}
