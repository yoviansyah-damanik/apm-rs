<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $connection = 'simrs';
    protected $table = 'pasien';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_rkm_medis';
    protected $keyType = 'string';

    public function registers(): HasMany
    {
        return $this->hasMany(Register::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function paytype()
    {
        return $this->belongsTo(PayType::class, 'kd_pj', 'kd_pj');
    }
}
