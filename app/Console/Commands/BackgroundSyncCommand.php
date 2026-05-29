<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\BackgroundSyncJob;

class BackgroundSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'background:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Background Synchronization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BackgroundSyncJob::dispatch();

        $this->info('Background Sync Job Dispatched Successfully');
    }
}