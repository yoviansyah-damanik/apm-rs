<?php

namespace App\Models;

use App\Helpers\SettingHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polyclinic extends Model
{
    protected $connection = 'simrs';
    protected $table = 'poliklinik';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_poli';
    protected $keyType = 'string';

    public function polyclinicsNameAs(): Attribute
    {
        return new Attribute(
            get: function () {
                $name = preg_replace('/^poliklinik\s*/i', '', $this->nm_poli);
                $name = trim($name);

                // Hilangkan semua kata Poliklinik dan tinggalkan kata setelahnya
                if (preg_match('/^([A-Z]+)\1$/i', $name, $matches)) {
                    $name = strtoupper($matches[1]);
                }

                return $name;
            }
        );
    }
    protected function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn($this->table . '.kd_poli', SettingHelper::getExcludePolyclinics())
            ->where('status', '1');
    }

    protected function scopeScheduledToday(Builder $query): Builder
    {
        return $query->whereHas(
            'schedules',
            fn($q) => $q->scheduledToday()
        )
            ->with(['schedules' => fn($q) => $q->scheduledToday()]);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'kd_poli', 'kd_poli');
    }

    public function bpjs(): HasOne
    {
        return $this->hasOne(BpjsPolyclinic::class, 'kd_poli_rs', 'kd_poli');
    }
}
