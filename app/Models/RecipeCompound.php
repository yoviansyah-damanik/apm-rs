<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeCompound extends Model
{
    protected $connection = 'simrs';
    protected $table = 'resep_dokter_racikan';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;
    protected $keyType = 'string';

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'no_resep', 'no_resep');
    }
}
