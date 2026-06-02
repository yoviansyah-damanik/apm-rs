<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsDoctor extends Model
{
    protected $connection = 'simrs';
    protected $table = 'maping_dokter_dpjpvclaim';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_dokter';
    protected $keyType = 'string';
}
