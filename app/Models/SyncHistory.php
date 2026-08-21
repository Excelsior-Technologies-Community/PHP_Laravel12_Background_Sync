<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncHistory extends Model
{
    protected $fillable = [
        'status',
        'duration',
        'started_at',
        'completed_at',
        'message',
        'progress',
        'sync_type',
        'date_range_start',
        'date_range_end',
        'is_paused',
        'is_cancelled',
        'log_details',
    ];

    protected $casts = [
        'is_paused'    => 'boolean',
        'is_cancelled' => 'boolean',
    ];
}
