<?php

use App\Jobs\CleanExpiredQueuesJob;
use App\Jobs\ExpandQueueToleranceJob;
use App\Jobs\ProcessMatchmakingJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Realtime matchmaking workers
// Scanner runs every 2 seconds as a safety net — most matches are created
// instantly via QueueManagerService::join() the moment the second team queues.
Schedule::job(new ProcessMatchmakingJob)
    ->everyTwoSeconds()
    ->withoutOverlapping(10)
    ->onOneServer()
    ->name('matchmaking-scanner');

Schedule::job(new ExpandQueueToleranceJob)
    ->everyThirtySeconds()
    ->withoutOverlapping(30)
    ->onOneServer()
    ->name('matchmaking-tolerance-expander');

Schedule::job(new CleanExpiredQueuesJob)
    ->everyMinute()
    ->withoutOverlapping(60)
    ->onOneServer()
    ->name('matchmaking-cleaner');
