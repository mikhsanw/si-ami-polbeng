<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateKriteria extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'bobot', 'instrumen_template_id', 'kriteria_id',
    ];
    
	public function instrumenTemplate()
	{
		return $this->belongsTo('App\Models\InstrumenTemplate');
	}

	public function kriteria()
	{
		return $this->belongsTo('App\Models\Kriteria');
	}
}
