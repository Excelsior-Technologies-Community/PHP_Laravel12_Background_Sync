<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BackgroundSyncService
{
    public function sync(): void
    {
        Log::info('Sync Service Started');

        // Simulate heavy sync task
        sleep(5);

        Log::info('Sync Service Completed');
    }
}