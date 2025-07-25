<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RubrikPenilaian extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'skor', 'deskripsi', 'formula_kondisi', 'indikator_id',
    ];
    
	public function indikator()
	{
		return $this->belongsTo('App\Models\Indikator');
	}
}
