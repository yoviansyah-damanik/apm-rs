<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlLetter extends Model
{
    protected $connection = 'simrs';
    protected $table = 'bridging_surat_kontrol_bpjs';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_sep';
    protected $keyType = 'string';

    /**
     * Relasi ke Sep
     */
    public function sep(): BelongsTo
    {
        return $this->belongsTo(Sep::class, 'no_sep', 'no_sep');
    }
}
