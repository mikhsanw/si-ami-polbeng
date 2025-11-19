<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = ['id' => 'string', 'parent_id' => 'string', 'user_id' => 'string'];

    protected $fillable = [
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

    public function auditPeriodes()
    {
        return $this->hasMany('App\Models\AuditPeriode');
    }
}
