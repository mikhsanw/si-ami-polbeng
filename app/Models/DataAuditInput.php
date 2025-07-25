<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataAuditInput extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $casts=[];

    protected $fillable=[
        'id', 'nilai_variable', 'hasil_audit_id', 'indikator_input_id',
    ];
    
    public function hasilaudit()
	{
		return $this->belongsTo('App\Models\HasilAudit');
	}

    public function indikatorinput()
    {
        return $this->belongsTo('App\Models\IndikatorInput');
    }

    public function getDataAuditInput($id)
    {
        return $this->where('id', $id)->first();
    }

    public function getAllDataAuditInputs()
    {
        return $this->all();
    }

}
