<?php

namespace App\Jobs;

use App\Services\QueueScannerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMatchmakingJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries     = 1;
    public int $timeout   = 25;
    public int $uniqueFor = 30;

    public function uniqueId(): string
    {
        return 'matchmaking-scanner';
    }

    public function handle(QueueScannerService $scanner): void
    {
        $scanner->scan();
    }
}
