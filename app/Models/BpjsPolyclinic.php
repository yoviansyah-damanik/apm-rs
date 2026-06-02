<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsPolyclinic extends Model
{
    protected $connection = 'simrs';
    protected $table = 'maping_poli_bpjs';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_poli_rs';
    protected $keyType = 'string';
}
