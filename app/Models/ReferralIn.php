<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralIn extends Model
{
    protected $connection = 'simrs';
    protected $table = 'rujuk_masuk';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_rawat';
    protected $keyType = 'string';
}
