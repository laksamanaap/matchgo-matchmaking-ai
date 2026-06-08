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

Schedule::job(new \App\Jobs\ProcessMatchmaking)->everyMinute();
