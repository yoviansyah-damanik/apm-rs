<?php

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Register extends Model
{
    protected $connection = 'simrs';
    protected $table = 'reg_periksa';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_rawat';
    protected $keyType = 'string';

    public function scopeActive($query, \Carbon\Carbon|Date|null $date = null)
    {
        $date ??= now();
        return $query->when($date, fn($q) => $q->whereDate('tgl_registrasi', $date))
            ->where('stts', '<>', 'Batal');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'kd_dokter', 'kd_dokter');
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'kd_poli', 'kd_poli');
    }

    public function payType(): BelongsTo
    {
        return $this->belongsTo(PayType::class, 'kd_pj', 'kd_pj');
    }

    public function jknRef(): BelongsTo
    {
        return $this->belongsTo(JknRef::class, 'no_rawat', 'no_rawat');
    }

    /**
     * Ambil schedule berdasarkan kd_poli dan kd_dokter
     * Karena menggunakan composite key, tidak bisa pakai relasi Laravel standar
     * Gunakan method getSchedule() atau load manual
     */
    public function getSchedule()
    {
        return Schedule::where('kd_poli', $this->kd_poli)
            ->where('kd_dokter', $this->kd_dokter)
            ->first();
    }

    public function recipe(): HasMany
    {
        return $this->hasMany(Recipe::class, 'no_rawat', 'no_rawat');
    }
}
