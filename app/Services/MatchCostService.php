<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\MatchCost;

class MatchCostService
{
    /**
     * Hitung dan simpan biaya pertandingan.
     *
     * total_cost     = venue.price_per_hour * (duration_minutes / 60)
     * cost_per_team  = total_cost / 2
     * cost_per_player = cost_per_team / jumlah pemain yang attended=true (fallback: semua terdaftar → team.player_count)
     */
    public function calculate(FutsalMatch $match): MatchCost
    {
        $match->loadMissing(['venue', 'matchPlayers', 'teamA']);

        $totalCost   = (int) ($match->venue->price_per_hour * ($match->duration_minutes / 60));
        $costPerTeam = (int) ($totalCost / 2);

        // Prioritas: pemain yang hadir (attended=true) → semua terdaftar → fallback team.player_count
        $attendedCount = $match->matchPlayers
            ->where('team_id', $match->team_a_id)
            ->where('attended', true)
            ->count();

        $teamAPlayerCount = $attendedCount > 0
            ? $attendedCount
            : ($match->matchPlayers->where('team_id', $match->team_a_id)->count() ?: ($match->teamA->player_count ?? 5));

        $costPerPlayer = (int) ($costPerTeam / $teamAPlayerCount);

        return MatchCost::updateOrCreate(
            ['match_id' => $match->id],
            [
                'total_cost'     => $totalCost,
                'cost_per_team'  => $costPerTeam,
                'cost_per_player' => $costPerPlayer,
            ]
        );
    }
}
