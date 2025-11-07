<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HasilAudit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'skor_auditee', 'skor_final', 'catatan_final', 'status_terkini', 'audit_periode_id', 'indikator_id',
    ];

    public function auditPeriode()
    {
        return $this->belongsTo('App\Models\AuditPeriode');
    }

    public function indikator()
    {
        return $this->belongsTo('App\Models\Indikator');
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function dataAuditInput()
    {
        return $this->hasMany('App\Models\DataAuditInput');
    }

    public function dataAuditInputForInput($indikatorInputId)
    {
        return $this->dataAuditInput()
            ->where('indikator_input_id', $indikatorInputId)
            ->first();
    }

    public function logAktivitasAudit()
    {
        return $this->hasMany('App\Models\LogAktivitasAudit');
    }

    public function getSkorFinalAttribute($value)
    {
        return number_format($value, 2, ',', '.');
    }

    public function getSkorAuditeeAttribute($value)
    {
        return number_format($value, 2, ',', '.');
    }

    public function getStatusTerkiniAttribute($value)
    {
        return config('master.content.hasil_audit.status_terkini')[$value] ?? $value;
    }
}
