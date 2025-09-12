<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenugasanAuditor extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'user_id', 'audit_periode_id',
    ];
    
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function auditPeriode()
	{
		return $this->belongsTo('App\Models\AuditPeriode');
	}
}
