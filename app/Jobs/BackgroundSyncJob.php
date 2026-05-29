<?php

namespace App\Jobs;

use App\Models\SyncHistory;
use App\Services\BackgroundSyncService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackgroundSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry failed jobs 3 times
     */
    public $tries = 3;

    /**
     * Job timeout
     */
    public $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();

        $history = SyncHistory::create([

            'status' => 'Running',

            'started_at' => $startTime,

            'message' => 'Background Sync Started',
        ]);

        try {

            Log::info('Background Sync Started');

            // Run sync service
            app(BackgroundSyncService::class)->sync();

            Log::info('Background Sync Completed');

            $endTime = now();

            $history->update([

                'status' => 'Completed',

                'completed_at' => $endTime,

                'duration' => $startTime->diffInSeconds($endTime),

                'message' => 'Background Sync Completed Successfully',
            ]);

        } catch (Exception $e) {

            $history->update([

                'status' => 'Failed',

                'completed_at' => now(),

                'message' => $e->getMessage(),
            ]);

            Log::error('Background Sync Failed: ' . $e->getMessage());

            throw $e;
        }
    }
}