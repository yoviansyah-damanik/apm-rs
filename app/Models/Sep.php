<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sep extends Model
{
    protected $connection = 'simrs';
    protected $table = 'bridging_sep';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'no_sep';
    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tglkkl' => 'date',
            'tglpulang' => 'datetime',
        ];
    }
}
