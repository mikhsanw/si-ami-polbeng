<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndikatorInput extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'nama_variable', 'label_input', 'tipe_data', 'indikator_id',
    ];
    
	public function indikator()
	{
		return $this->belongsTo('App\Models\Indikator');
	}
}
