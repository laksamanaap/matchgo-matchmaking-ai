<?php

namespace App\Services;

use App\Events\MatchFoundEvent;
use App\Models\MatchmakingMatch;
use App\Models\MatchmakingQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueScannerService
{
    public const ACCEPT_WINDOW_SECONDS = 30;

    public function __construct(private CompatibilityEngine $compatibility) {}

    /**
     * Iterate waiting queues oldest-first and pair the highest-scoring compatible
     * pairs. Each pair is created under an atomic cache lock so two concurrent
     * scanner runs cannot match the same queue twice.
     */
    public function scan(): int
    {
        $waiting = MatchmakingQueue::where('status', 'waiting')
            ->orderBy('queued_at')
            ->get();

        if ($waiting->count() < 2) {
            return 0;
        }

        $matchedQueueIds = [];
        $createdCount    = 0;

        foreach ($waiting as $a) {
            if (in_array($a->id, $matchedQueueIds, true)) {
                continue;
            }

            $bestB        = null;
            $bestScore    = 0.0;

            foreach ($waiting as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if (in_array($b->id, $matchedQueueIds, true)) {
                    continue;
                }

                $score = $this->compatibility->score($a, $b);
                if ($score === null) {
                    continue;
                }
                if ($score < CompatibilityEngine::MATCH_THRESHOLD) {
                    continue;
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestB     = $b;
                }
            }

            if (! $bestB) {
                continue;
            }

            $created = $this->createMatchSafely($a, $bestB, $bestScore);

            if ($created) {
                $matchedQueueIds[] = $a->id;
                $matchedQueueIds[] = $bestB->id;
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Acquire a per-pair atomic lock, recheck queue states inside a DB
     * transaction, then create the matchmaking match atomically. This protects
     * against two scanner workers racing for the same queue rows.
     */
    private function createMatchSafely(MatchmakingQueue $a, MatchmakingQueue $b, float $score): ?MatchmakingMatch
    {
        // Order ids for a stable lock key
        [$lowId, $highId] = $a->id < $b->id ? [$a->id, $b->id] : [$b->id, $a->id];
        $lockKey = "matchmaking:pair:{$lowId}:{$highId}";

        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return null;
        }

        try {
            return DB::transaction(function () use ($a, $b, $score) {
                $freshA = MatchmakingQueue::lockForUpdate()->find($a->id);
                $freshB = MatchmakingQueue::lockForUpdate()->find($b->id);

                if (! $freshA || ! $freshB) {
                    return null;
                }

                if ($freshA->status !== 'waiting' || $freshB->status !== 'waiting') {
                    return null;
                }

                $match = MatchmakingMatch::create([
                    'queue_a_id'          => $freshA->id,
                    'queue_b_id'          => $freshB->id,
                    'compatibility_score' => $score,
                    'status'              => 'pending',
                    'expires_at'          => now()->addSeconds(self::ACCEPT_WINDOW_SECONDS),
                ]);

                $freshA->update(['status' => 'matched']);
                $freshB->update(['status' => 'matched']);

                $match->loadMissing(['queueA.team', 'queueB.team']);

                broadcast(new MatchFoundEvent($match, 'a'));
                broadcast(new MatchFoundEvent($match, 'b'));

                Log::info('Matchmaking pair created', [
                    'match_id' => $match->id,
                    'team_a'   => $freshA->team_id,
                    'team_b'   => $freshB->team_id,
                    'score'    => $score,
                ]);

                return $match;
            });
        } finally {
            optional($lock)->release();
        }
    }
}
