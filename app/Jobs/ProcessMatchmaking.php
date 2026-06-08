<?php

namespace App\Jobs;

use App\Models\AutoMatchmakingQueue;
use App\Services\MatchmakingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMatchmaking implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?int $queueId = null)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(MatchmakingService $service): void
    {
        $service->expireStaleQueues();

        $queues = AutoMatchmakingQueue::query()
            ->whereIn('status', ['waiting', 'searching'])
            ->when($this->queueId, fn ($query) => $query->whereKey($this->queueId))
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->oldest()
            ->limit($this->queueId ? 1 : 50)
            ->get();

        foreach ($queues as $queue) {
            $service->processQueue($queue);
        }
    }
}
