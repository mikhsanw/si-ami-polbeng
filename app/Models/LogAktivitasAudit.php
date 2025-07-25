<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogAktivitasAudit extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'tipe_aksi', 'catatan_aksi', 'hasil_audit_id', 'user_id',
    ];
    
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function hasilaudit()
	{
		return $this->belongsTo('App\Models\HasilAudit');
	}
}
