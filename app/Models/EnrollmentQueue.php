<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentQueue extends Model
{
    protected $connection = 'simrs';
    protected $table = 'antripendaftaran_nomor';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'nomor';
    protected $keyType = 'string';
}
