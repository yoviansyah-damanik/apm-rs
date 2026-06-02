<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $connection = 'simrs';
    protected $table = 'dokter';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_dokter';
    protected $keyType = 'string';

    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '1');
    }

    protected function scopeScheduledToday(Builder $query): Builder
    {
        return $query->whereHas(
            'schedules',
            fn($q) => $q->scheduledToday()
        );
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'kd_dokter', 'kd_dokter');
    }

    public function bpjs(): HasOne
    {
        return $this->hasOne(BpjsDoctor::class, 'kd_dokter', 'kd_dokter');
    }
}
