<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditPeriode extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'tahun_akademik', 'status', 'unit_id', 'instrument_template_id',
    ];
    
	public function instrumenttemplate()
	{
		return $this->belongsTo('App\Models\InstrumentTemplate');
	}

	public function unit()
	{
		return $this->belongsTo('App\Models\Unit');
	}
}
