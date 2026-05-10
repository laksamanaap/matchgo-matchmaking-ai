<?php

namespace App\Services;

use App\Models\MatchmakingHistory;
use App\Models\MatchmakingQueue;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;

class QueueManagerService
{
    public function __construct(private QueueScannerService $scanner) {}

    /**
     * Push a team's captain into the matchmaking queue.
     * Idempotent: if a waiting queue already exists for the team it is reused.
     *
     * After joining, immediately runs one scan pass — if there's an opponent
     * already waiting, the match is created in the same request (no waiting
     * for the next 5-second scheduler tick).
     */
    public function join(Team $team, User $captain): MatchmakingQueue
    {
        $existing = MatchmakingQueue::where('team_id', $team->id)
            ->where('status', 'waiting')
            ->first();

        if ($existing) {
            $this->scanner->scan();

            return $existing;
        }

        $queue = MatchmakingQueue::create([
            'team_id'         => $team->id,
            'captain_id'      => $captain->id,
            'skill_level'     => $team->skill_level,
            'latitude'        => $team->latitude,
            'longitude'       => $team->longitude,
            'search_range_km' => 5,
            'level_tolerance' => 1,
            'status'          => 'waiting',
        ]);

        // Fire an immediate scan so the joiner gets paired against any
        // opponent already in queue without waiting for the scheduler.
        $this->scanner->scan();

        return $queue;
    }

    /**
     * Pull a team out of the queue. Records cancellation in history.
     */
    public function leave(MatchmakingQueue $queue): void
    {
        if ($queue->status !== 'waiting') {
            return;
        }

        $duration = $queue->queued_at?->diffInSeconds(now()) ?? 0;

        $queue->update(['status' => 'cancelled']);

        MatchmakingHistory::create([
            'team_id'                => $queue->team_id,
            'matched_team_id'        => null,
            'compatibility_score'    => null,
            'queue_duration_seconds' => $duration,
            'result'                 => 'cancelled',
        ]);
    }

    /**
     * Read-only queue snapshot for a team.
     */
    public function getStatus(Team $team): array
    {
        $queue = MatchmakingQueue::where('team_id', $team->id)
            ->where('status', 'waiting')
            ->first();

        if (! $queue) {
            return ['state' => 'idle'];
        }

        $position = MatchmakingQueue::where('status', 'waiting')
            ->where('queued_at', '<=', $queue->queued_at)
            ->count();

        $queuedAtMs  = $queue->queued_at ? $queue->queued_at->valueOf() : (int) (microtime(true) * 1000);
        $waitSeconds = max(0, (int) floor((time() - ($queuedAtMs / 1000))));

        return [
            'state'           => 'queued',
            'queue_id'        => $queue->id,
            'position'        => $position,
            'wait_seconds'    => $waitSeconds,
            'queued_at_ms'    => $queuedAtMs,
            'level_tolerance' => $queue->level_tolerance,
            'search_range_km' => $queue->search_range_km,
            'expires_at'      => $queue->expires_at?->toIso8601String(),
        ];
    }

    /**
     * Estimated wait time across active queue (very simple heuristic).
     */
    public function averageWaitSeconds(Carbon $since): int
    {
        $rows = MatchmakingHistory::where('result', 'accepted')
            ->where('created_at', '>=', $since)
            ->pluck('queue_duration_seconds');

        if ($rows->isEmpty()) {
            return 0;
        }

        return (int) round($rows->avg());
    }
}
