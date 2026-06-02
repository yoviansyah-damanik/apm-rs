<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Icd10 extends Model
{
    protected $connection = 'simrs';
    protected $table = 'penyakit';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'kd_penyakit';
    protected $keyType = 'string';

    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /** Cari berdasarkan kode atau nama penyakit */
    protected function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('kd_penyakit', 'like', "%{$keyword}%")
              ->orWhere('nm_penyakit', 'like', "%{$keyword}%");
        });
    }
}
