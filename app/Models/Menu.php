<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    
    protected $fillable=[
        'id', 'parent_id', 'title', 'subtitle', 'code', 'url', 'model', 'icon', 'type', 'show', 'active', 'sort',
    ];
    protected $casts=[
        'show'=>'boolean', 'active'=>'boolean', 'id'=>'string','parent_id'=>'string',
    ];
    protected $hidden=[
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function parent() : object
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children() : object
    {
        return $this->hasMany(Menu::class, 'parent_id')->sort();
    }

    public function scopeSort($query) : object
    {
        return $query->orderBy('sort', 'asc');
    }

    public function scopeActive($query) : object
    {
        return $query->where('active', TRUE);
    }

    public function scopeShow($query) : object
    {
        return $query->where('show', TRUE);
    }

    public function getModelAttribute(): string
    {
        return Str::replace('/', '\\', config('master.app.root.model')) . '\\' . $this->attributes['model'];
    }

    public function active($kode)
    {
        $current	=	explode(".", Route::currentRouteName());
        if($menus	=	$this->whereCode($current[0])->first()) {
            $response= $kode == $current[0] ? 'active' : $this->getParent($this->id,$menus);
        }
        return $response ?? '';
    }

    private function getParent($id,$menus)
    {
        if ($id == $menus->parent_id) {
            return 'show';
        }else{
            return $menus->parent_id ? $this->getParent($id,$menus->parent) : '';
        }
    }
}
