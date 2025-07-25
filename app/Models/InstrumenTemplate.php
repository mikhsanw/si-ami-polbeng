<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstrumenTemplate extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'nama', 'deskripsi', 'is_active', 'lembaga_akreditasi_id',
    ];
    
	public function lembagaakreditasi()
	{
		return $this->belongsTo('App\Models\LembagaAkreditasi');
	}

	public function templatekriterias()
	{
		return $this->hasMany('App\Models\TemplateKriteria');
	}
}
