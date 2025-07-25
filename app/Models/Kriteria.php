<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kriteria extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'kode', 'nama',
    ];
    
	public function templatekriterias()
	{
		return $this->hasMany('App\Models\TemplateKriteria');
	}

	public function indikators()
	{
		return $this->hasMany('App\Models\Indikator');
	}
}
