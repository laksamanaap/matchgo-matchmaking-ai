<?php

namespace App\Jobs;

use App\Events\MatchRejectedEvent;
use App\Events\QueueTimeoutEvent;
use App\Models\MatchmakingHistory;
use App\Models\MatchmakingMatch;
use App\Models\MatchmakingQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CleanExpiredQueuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    /**
     * Two responsibilities:
     *  1. Expire pending matchmaking_matches whose accept window has passed.
     *  2. Expire matchmaking_queues that exceeded their queued_at + 10 min lifetime.
     */
    public function handle(): void
    {
        $this->timeoutPendingMatches();
        $this->expireOldQueues();
    }

    private function timeoutPendingMatches(): void
    {
        $expired = MatchmakingMatch::with(['queueA', 'queueB'])
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $match) {
            DB::transaction(function () use ($match) {
                $match->update(['status' => 'timeout']);

                // Return queues to waiting OR cancel them depending on who accepted
                $this->resolveQueueAfterTimeout($match->queueA, $match->accepted_by_a);
                $this->resolveQueueAfterTimeout($match->queueB, $match->accepted_by_b);
            });

            broadcast(new MatchRejectedEvent($match, 'timeout'));
        }
    }

    private function resolveQueueAfterTimeout(?MatchmakingQueue $queue, bool $accepted): void
    {
        if (! $queue) {
            return;
        }

        if ($accepted) {
            // Captain DID accept — opponent timed out. Put captain back in queue.
            $queue->update(['status' => 'waiting']);

            return;
        }

        // Captain didn't even accept. Cancel the queue and log to history.
        $queue->update(['status' => 'cancelled']);

        MatchmakingHistory::create([
            'team_id'                => $queue->team_id,
            'matched_team_id'        => null,
            'compatibility_score'    => null,
            'queue_duration_seconds' => $queue->queued_at?->diffInSeconds(now()) ?? 0,
            'result'                 => 'timeout',
        ]);
    }

    private function expireOldQueues(): void
    {
        $expired = MatchmakingQueue::where('status', 'waiting')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $queue) {
            $queue->update(['status' => 'expired']);

            MatchmakingHistory::create([
                'team_id'                => $queue->team_id,
                'matched_team_id'        => null,
                'compatibility_score'    => null,
                'queue_duration_seconds' => $queue->queued_at?->diffInSeconds(now()) ?? 0,
                'result'                 => 'timeout',
            ]);

            broadcast(new QueueTimeoutEvent($queue));
        }
    }
}
