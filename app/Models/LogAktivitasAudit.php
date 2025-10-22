<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogAktivitasAudit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id', 'tipe_aksi', 'catatan_aksi', 'hasil_audit_id', 'user_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function hasilaudit()
    {
        return $this->belongsTo('App\Models\HasilAudit');
    }

    public function getTipeAksiAttribute($value)
    {
        return config('master.content.log_aktivitas_audit.tipe_aksi')[$value] ?? $value;
    }

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
