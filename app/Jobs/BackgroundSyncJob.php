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

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public string $syncType = 'General',
        public ?string $dateRangeStart = null,
        public ?string $dateRangeEnd = null,
        public ?int $historyId = null
    ) {}

    public function handle(): void
    {
        $startTime = now();
        $logs = [];

        // Retry case: reuse existing history record
        if ($this->historyId) {
            $history = SyncHistory::find($this->historyId);
            if ($history) {
                $history->update([
                    'status'       => 'Running',
                    'is_paused'    => false,
                    'is_cancelled' => false,
                    'progress'     => 0,
                    'started_at'   => $startTime,
                    'completed_at' => null,
                    'message'      => 'Retrying Sync...',
                ]);
            }
        }

        if (!isset($history) || !$history) {
            $history = SyncHistory::create([
                'status'           => 'Running',
                'started_at'       => $startTime,
                'message'          => 'Background Sync Started',
                'sync_type'        => $this->syncType,
                'date_range_start' => $this->dateRangeStart,
                'date_range_end'   => $this->dateRangeEnd,
                'progress'         => 0,
            ]);
        }

        try {
            $logs[] = '[' . now() . '] Sync Started - Type: ' . $this->syncType;
            Log::info('Background Sync Started', ['type' => $this->syncType]);

            // Simulate 10 steps with progress + pause/cancel check
            for ($i = 1; $i <= 10; $i++) {
                $history->refresh();

                if ($history->is_cancelled) {
                    $logs[] = '[' . now() . '] Sync Cancelled by user';
                    $history->update([
                        'status'       => 'Failed',
                        'completed_at' => now(),
                        'message'      => 'Sync Cancelled by User',
                        'log_details'  => implode("\n", $logs),
                    ]);
                    return;
                }

                while ($history->is_paused) {
                    sleep(2);
                    $history->refresh();
                    if ($history->is_cancelled) break;
                }

                app(BackgroundSyncService::class)->syncStep($i, $this->dateRangeStart, $this->dateRangeEnd);

                $progress = $i * 10;
                $logs[] = '[' . now() . '] Step ' . $i . ' completed - Progress: ' . $progress . '%';

                $history->update(['progress' => $progress]);
                sleep(1);
            }

            $endTime = now();
            $logs[] = '[' . now() . '] Sync Completed Successfully';

            $history->update([
                'status'       => 'Completed',
                'completed_at' => $endTime,
                'duration'     => $startTime->diffInSeconds($endTime),
                'message'      => 'Background Sync Completed Successfully',
                'progress'     => 100,
                'log_details'  => implode("\n", $logs),
            ]);

            Log::info('Background Sync Completed');

        } catch (Exception $e) {
            $logs[] = '[' . now() . '] ERROR: ' . $e->getMessage();
            $history->update([
                'status'       => 'Failed',
                'completed_at' => now(),
                'message'      => $e->getMessage(),
                'log_details'  => implode("\n", $logs),
            ]);
            Log::error('Background Sync Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
