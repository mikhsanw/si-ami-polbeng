<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=['id' => 'string', 'parent_id' => 'string', 'user_id' => 'string'];

    protected $fillable=[
        'id', 'nama', 'tipe', 'parent_id', 'user_id',
    ];
    
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('nama');
    }
    
}
