<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorInput extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [];

    protected $fillable = [
        'id', 'nama_variable', 'label_input', 'tipe_data', 'indikator_id', 'urutan',
    ];

    public function indikator()
    {
        return $this->belongsTo('App\Models\Indikator');
    }
}
