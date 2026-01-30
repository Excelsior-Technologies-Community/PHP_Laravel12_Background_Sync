# PHP_Laravel12_Background_Sync

# Step 1: Install Fresh Laravel 12 Application

Open Terminal / Command Prompt and run:
```php
    composer create-project laravel/laravel:^12.0 PHP_Laravel12_Background_Sync
```
Move into the project folder:
```php
      cd PHP_Laravel12_Background_Sync
```
Generate application key:
```php
        php artisan key:generate
```

# Step 2: Configure Database (.env)
Open .env file and update database settings:
```php
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3307
DB_DATABASE=laravel12_background_sync
DB_USERNAME=root
DB_PASSWORD=
```
Run database migration:
```php
php artisan migrate
```

# Step 3: Understand Laravel 12 Background Sync Structure
Laravel 12 handles background synchronization using Scheduler + Command + Job + Queue + Service architecture.

Main Components:
```php
⦁ Scheduler – decides when sync runs
⦁ Command – triggers background job
⦁ Job – runs process in background
⦁ Queue – stores background jobs
⦁ Service – contains actual synchronization logic
```

Flow:
```php
Scheduler → Command → Job → Queue → Worker → Sync Logic
```

# Step 4: Configure Queue System
Open .env file and set queue driver:
```php
QUEUE_CONNECTION=database
```
Create queue table:
```php
       php artisan queue:table
       php artisan migrate
```

# Step 5: Create Background Sync Job
Generate a job class:
```php
                php artisan make:job BackgroundSyncJob
```
Explanation:
```php
⦁ This command creates a Job class used for executing tasks in the background.
⦁ The job runs asynchronously using Laravel Queue without blocking user requests.
⦁ It is ideal for handling time-consuming operations like data synchronization, API calls, or heavy processing.
⦁ The generated job file is stored in the app/Jobs directory.
```
File Path:
```php
              app/Jobs/BackgroundSyncJob.php
```
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackgroundSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Example: background sync logic
        Log::info('Background Sync Started');

        sleep(5); // simulate heavy task

        Log::info('Background Sync Completed');
    }
}

```
⦁ This job will run synchronization in background.


# Step 6: Create Artisan Command for Sync
Generate custom command:
```php
          php artisan make:command BackgroundSyncCommand
```
Explanation:
```php
⦁ This command creates a custom Artisan command for triggering background processes.
⦁ It acts as a bridge between the scheduler and the background job.
⦁ The command is used to dispatch the background sync job programmatically or on a schedule.
⦁ The generated command file is stored in the app/Console/Commands directory.
```
File Path:
```php
app/Console/Commands/BackgroundSyncCommand.php
```
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackgroundSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:background-sync-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
  public function handle()
{
    \App\Jobs\BackgroundSyncJob::dispatch();
}
}
```
This command dispatches the background job.

# Step 7: Configure Scheduler
⦁ Laravel 12 does not use Console/Kernel.php for scheduling.
Open File:
```php
      routes/console.php
```
Add scheduler entry:
```php
 Schedule::command('background:sync')->everyMinute();
```
⦁ This schedules the background sync automatically.

# Step 8: Run Queue Worker
⦁ Background jobs require a queue worker.
Open terminal and run:
```php
           php artisan queue:work
```
⦁ Keep this terminal running.

# Step 9: Run Scheduler
Open a new terminal window and run:
```php
      php artisan schedule:work
```
⦁ This executes scheduled background sync tasks.

# Step 10: Manual Sync Test
Run command manually:
```php
     php artisan background:sync
```
Check logs:
```php
    storage/logs/laravel.log
```

# Step 11: Run Laravel 12 Project
Start server:
```php
       php artisan serve
```
Open browser:
```php
  http://127.0.0.1:8000
```
<img width="692" height="372" alt="image" src="https://github.com/user-attachments/assets/3cd297ca-34ed-43b1-a3f9-0df202f56614" />

# Explenation :
```php
⦁ This response confirms that the background sync has started successfully.
⦁ The request returns immediately without waiting for the sync to finish.
⦁ The synchronization runs asynchronously in the background using Laravel Queue.
```

# Project Folder Structure:
```php
PHP_Laravel12_Background_Sync
├── app/
│   ├── Jobs/
│   │   └── BackgroundSyncJob.php
│   │
│   ├── Console/
│   │   └── Commands/
│   │       └── BackgroundSyncCommand.php
│   │
│   ├── Services/
│   │   └── BackgroundSyncService.php
│
├── routes/
│   ├── web.php
│   └── console.php
│
├── database/
│   └── migrations/
│       └── *_create_jobs_table.php
│
├── storage/
│   └── logs/
│       └── laravel.log
│
├── .env
│
└── artisan
```



