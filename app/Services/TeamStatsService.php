<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\TeamStats;

class TeamStatsService
{
    /**
     * Update statistik kedua tim setelah skor audit disetujui.
     */
    public function updateFromMatch(FutsalMatch $match): void
    {
        $scoreA = (int) $match->score_a;
        $scoreB = (int) $match->score_b;

        if ($scoreA > $scoreB) {
            $resultA = 'win';
            $resultB = 'loss';
        } elseif ($scoreA < $scoreB) {
            $resultA = 'loss';
            $resultB = 'win';
        } else {
            $resultA = 'draw';
            $resultB = 'draw';
        }

        $this->applyResult($match->team_a_id, $resultA, $scoreA, $scoreB);
        $this->applyResult($match->team_b_id, $resultB, $scoreB, $scoreA);
    }

    private function applyResult(int $teamId, string $result, int $goalsFor, int $goalsAgainst): void
    {
        $stats = TeamStats::firstOrCreate(
            ['team_id' => $teamId],
            ['total_matches' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'goals_scored' => 0, 'goals_conceded' => 0]
        );

        $stats->increment('total_matches');
        $stats->increment('goals_scored', $goalsFor);
        $stats->increment('goals_conceded', $goalsAgainst);

        match ($result) {
            'win'  => $stats->increment('wins'),
            'loss' => $stats->increment('losses'),
            'draw' => $stats->increment('draws'),
        };
    }
}
