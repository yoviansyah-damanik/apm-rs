<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chronic extends Model
{
    protected $connection = 'simrs';
    protected $table = 'pasien_kronis';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_rawat';
    protected $keyType = 'string';
}
