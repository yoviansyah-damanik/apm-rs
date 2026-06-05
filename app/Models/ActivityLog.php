<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'mariadb';
    protected $table = 'apm_activity_logs';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
