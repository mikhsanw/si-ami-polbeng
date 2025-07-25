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
}
