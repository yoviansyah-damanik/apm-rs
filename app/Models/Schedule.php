<?php

namespace App\Models;

use App\Helpers\MagicHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    protected $connection = 'simrs';
    protected $table = 'jadwal';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_poli';
    protected $keyType = 'string';

    // public $with = ['polyclinic', 'doctor'];

    protected function scopeScheduledToday(Builder $query): Builder
    {
        return $query->with([
            'polyclinic' => fn($q) => $q->active(),
            'doctor' => fn($q) => $q->active(),
        ])
            ->whereHas(
                'polyclinic',
                fn($q) => $q->active(),
            )
            ->whereHas(
                'doctor',
                fn($q) => $q->active(),
            )
            ->where('hari_kerja', MagicHelper::getDaysName(\Carbon\Carbon::now()->dayOfWeek));
    }

    protected function scopeLimitedTime(Builder $query): Builder
    {
        return $query->whereRaw('NOW() BETWEEN DATE_SUB(jam_mulai, INTERVAL 30 MINUTE) AND jam_selesai');
    }

    public function polyclinic(): HasOne
    {
        return $this->hasOne(Polyclinic::class, 'kd_poli', 'kd_poli');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'kd_dokter', 'kd_dokter');
    }
}
