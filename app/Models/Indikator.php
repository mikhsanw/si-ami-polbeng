<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Indikator extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
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

    public function hasilAuditForPeriode($auditPeriodeId)
    {
        return $this->hasOne('App\Models\HasilAudit')
                    ->where('audit_periode_id', $auditPeriodeId)
                    ->first();
    }
}
