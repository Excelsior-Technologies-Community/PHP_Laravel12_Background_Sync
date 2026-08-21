<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BackgroundSyncService
{
    public function sync(): void
    {
        Log::info('Sync Service Started');
        sleep(5);
        Log::info('Sync Service Completed');
    }

    public function syncStep(int $step, ?string $dateStart = null, ?string $dateEnd = null): void
    {
        Log::info("Sync Step {$step} - Range: {$dateStart} to {$dateEnd}");
        // Simulate per-step work
        usleep(500000); // 0.5 sec
    }
}
