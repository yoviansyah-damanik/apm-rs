<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JknRef extends Model
{
    protected $connection = 'simrs';
    protected $table = 'referensi_mobilejkn_bpjs';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'nobooking';
    protected $keyType = 'string';

    public function register(): HasOne
    {
        return $this->hasOne(Register::class, 'no_rawat', 'no_rawat');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'no_rkm_medis', 'norm');
    }
}
