<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyQueue extends Model
{
    protected $connection = 'simrs';
    protected $table = 'antrifarmasi';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_resep';
    protected $keyType = 'string';
}
