<?php

namespace App\Jobs;

use App\Models\MatchmakingQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpandQueueToleranceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout   = 20;
    public int $uniqueFor = 30;

    public function uniqueId(): string
    {
        return 'matchmaking-tolerance-expander';
    }

    /**
     * Gradually widen tolerance for queues that have been waiting longer.
     *
     *   0–30s : level=1, range=5
     *  30–60s : level=2, range=15
     *   60s+  : level=3, range=50
     */
    public function handle(): void
    {
        $now = now();

        MatchmakingQueue::where('status', 'waiting')
            ->where('queued_at', '<=', $now->copy()->subSeconds(60))
            ->where(function ($q) {
                $q->where('level_tolerance', '<', 3)
                  ->orWhere('search_range_km', '<', 50);
            })
            ->update([
                'level_tolerance' => 3,
                'search_range_km' => 50,
            ]);

        MatchmakingQueue::where('status', 'waiting')
            ->whereBetween('queued_at', [
                $now->copy()->subSeconds(60),
                $now->copy()->subSeconds(30),
            ])
            ->where(function ($q) {
                $q->where('level_tolerance', '<', 2)
                  ->orWhere('search_range_km', '<', 15);
            })
            ->update([
                'level_tolerance' => 2,
                'search_range_km' => 15,
            ]);
    }
}
