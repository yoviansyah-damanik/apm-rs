<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayType extends Model
{

    protected $connection = 'simrs';
    protected $table = 'penjab';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_pj';
    protected $keyType = 'string';


    protected function scopeActive($query)
    {
        $query->where('status', 1);
    }
}
