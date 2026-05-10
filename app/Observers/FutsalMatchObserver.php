<?php

namespace App\Observers;

use App\Models\FutsalMatch;
use App\Models\MatchScoreAudit;
use App\Services\TeamStatsService;

class FutsalMatchObserver
{
    /**
     * When a match is marked completed with scores, auto-approve the audit and
     * update team statistics immediately. No manual audit step required.
     * If scores change later the old result is reversed before re-applying.
     */
    public function updated(FutsalMatch $match): void
    {
        $statusChanged = $match->wasChanged('status') && $match->status === 'completed';
        $scoresChanged = $match->wasChanged(['score_a', 'score_b'])
                      && $match->status === 'completed';

        if (! $statusChanged && ! $scoresChanged) {
            return;
        }

        // Scores must be present to calculate result
        if ($match->score_a === null || $match->score_b === null) {
            return;
        }

        $audit = MatchScoreAudit::firstOrCreate(
            ['match_id' => $match->id],
            ['status' => 'pending']
        );

        // If scores were re-entered, reverse the previous stats first
        if ($audit->status === 'approved' && $scoresChanged && ! $statusChanged) {
            app(TeamStatsService::class)->reverseFromMatch(
                $match->team_a_id,
                $match->team_b_id,
                (int) $match->getOriginal('score_a'),
                (int) $match->getOriginal('score_b'),
            );
        }

        // Auto-approve and update stats
        $audit->update([
            'status'     => 'approved',
            'auditor_id' => auth()->id(),
        ]);

        app(TeamStatsService::class)->updateFromMatch($match);
    }
}
