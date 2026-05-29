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
    ];
}