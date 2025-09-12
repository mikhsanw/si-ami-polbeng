<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HasilAudit extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'skor_auditee', 'skor_final', 'catatan_final', 'status_terkini', 'audit_periode_id', 'indikator_id',
    ];
    
	public function auditperiode()
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
		return $this->hasOne('App\Models\DataAuditInput');
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
