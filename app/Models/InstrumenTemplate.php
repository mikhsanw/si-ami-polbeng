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
    
	public function lembagaAkreditasi()
	{
		return $this->belongsTo('App\Models\LembagaAkreditasi');
	}

	public function templateKriterias()
	{
		return $this->hasMany('App\Models\TemplateKriteria');
	}

	public function kriterias()
	{
		return $this->belongsToMany('App\Models\Kriteria', 'template_kriterias', 'instrumen_template_id', 'kriteria_id')
					->withPivot('bobot'); // Penting untuk bisa mengambil data bobot
	}

	public function auditPeriodes()
	{
		return $this->hasMany('App\Models\AuditPeriode');
	}
	
}
