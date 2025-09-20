<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateIndikator extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'bobot', 'instrumen_template_id', 'indikator_id',
    ];
    
	public function instrumenTemplate()
	{
		return $this->belongsTo('App\Models\InstrumenTemplate');
	}

	public function indikator()
	{
		return $this->belongsTo('App\Models\Indikator');
	}
}
