<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berita extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['id','judul','isi','tanggal','view'];
    protected $casts = [];
    protected $table = 'beritas';

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $judulSlug = Str::slug($model->judul, '-');
            $cek = self::where('slug', $judulSlug)->first();
            if ($cek) {
                $judulSlug .= '-' . date('d-m-Y');
            }
            $model->slug = $judulSlug;
        });
    }

}
