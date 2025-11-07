<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kriteria extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'kode', 'nama', 'parent_id', 'lembaga_akreditasi_id',
    ];

    public function lembagaAkreditasi()
    {
        return $this->belongsTo('App\Models\LembagaAkreditasi');
    }

    public function templateKriterias()
    {
        return $this->hasMany('App\Models\TemplateKriteria');
    }

    public function indikators()
    {
        return $this->hasMany('App\Models\Indikator');
    }

    public function rubrikPenilaians()
    {
        return $this->hasMany(RubrikPenilaian::class);
    }

    public function indikatorInputs()
    {
        return $this->hasMany(IndikatorInput::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        // Memanggil relasi 'children' dan juga relasi 'childrenRecursive' di dalamnya.
        return $this->children()->with('childrenRecursive');
    }

    public function parentRecursive()
    {
        // Memanggil relasi parent() dan juga memuat relasi parentRecursive dari induk tersebut
        return $this->parent()->with('parentRecursive');
    }
}
