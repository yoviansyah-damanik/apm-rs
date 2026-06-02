<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnose extends Model
{
    protected $connection = 'simrs';
    protected $table = 'diagnosa_pasien';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;
    protected $keyType = 'string';
}
