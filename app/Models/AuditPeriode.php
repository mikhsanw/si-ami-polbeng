<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditPeriode extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'tahun_akademik', 'status', 'unit_id', 'instrumen_template_id',
    ];

    protected $appends = ['periode_unit'];

    public function getPeriodeUnitAttribute()
    {
        return $this->tahun_akademik.' ('.$this->unit->nama.')';
    }

    public function instrumenTemplate()
    {
        return $this->belongsTo('\App\Models\InstrumenTemplate');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit');
    }

    public function hasilaudits()
    {
        return $this->hasMany('App\Models\HasilAudit');
    }

    public function penugasanAuditors()
    {
        return $this->hasMany('App\Models\PenugasanAuditor');
    }
}
