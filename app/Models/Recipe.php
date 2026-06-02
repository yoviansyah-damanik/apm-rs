<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $connection = 'simrs';
    protected $table = 'resep_obat';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_resep';
    protected $keyType = 'string';

    public function queue(): HasOne
    {
        return $this->hasOne(PharmacyQueue::class, 'no_resep', 'no_resep');
    }

    public function compounds(): HasMany
    {
        return $this->hasMany(RecipeCompound::class, 'no_resep', 'no_resep');
    }

    /**
     * Mendapatkan tipe resep berdasarkan ada/tidaknya racikan
     */
    public function getTipeResepAttribute(): string
    {
        return $this->compounds()->exists() ? 'Racikan' : 'Non Racikan';
    }
}
