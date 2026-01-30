<?php

namespace App\Http\Controllers;

use App\Jobs\BackgroundSyncJob;

class SyncController extends Controller
{
    public function startSync()
    {
        BackgroundSyncJob::dispatch();
        return response()->json([
            'status' => 'Background Sync Started'
        ]);
    }
}
